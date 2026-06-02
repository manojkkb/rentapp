import crypto from 'crypto';
import cors from 'cors';
import dotenv from 'dotenv';
import express from 'express';
import fs from 'fs';
import { createServer as createHttpServer } from 'http';
import { createServer as createHttpsServer } from 'https';
import path from 'path';
import { fileURLToPath } from 'url';
import { Server } from 'socket.io';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
// Laravel .env first, then socket-server/.env (host/port/ssl overrides only)
dotenv.config({ path: path.resolve(__dirname, '../.env') });
dotenv.config({ path: path.resolve(__dirname, '.env') });

const isProduction = process.env.APP_ENV === 'production';
const PORT = Number(process.env.SOCKET_PORT || 6001);
const BROADCAST_PORT = Number(process.env.SOCKET_BROADCAST_PORT || 6002);
const SECRET = process.env.SOCKET_SERVER_SECRET || '';
const APP_URL = process.env.APP_URL || 'http://127.0.0.1:8000';
const SOCKET_SERVER_URL = process.env.SOCKET_SERVER_URL || '';
const SSL_KEY_PATH = process.env.SOCKET_SSL_KEY || '';
const SSL_CERT_PATH = process.env.SOCKET_SSL_CERT || '';

const devOrigins = [
    'http://127.0.0.1:8000',
    'http://localhost:8000',
    'http://127.0.0.1:5173',
    'http://localhost:5173',
];

function useTls() {
    return Boolean(SSL_KEY_PATH && SSL_CERT_PATH);
}

if (isProduction && !useTls()) {
    console.error('APP_ENV=production requires SOCKET_SSL_KEY and SOCKET_SSL_CERT.');
    process.exit(1);
}

if (!SECRET) {
    console.error('SOCKET_SERVER_SECRET is missing. Set it in project .env (same value Laravel uses).');
    process.exit(1);
}

/** @param {string} url */
function originVariants(url) {
    const origins = new Set();
    if (!url) {
        return origins;
    }

    try {
        const parsed = new URL(url);
        origins.add(parsed.origin);

        const { hostname, protocol, port } = parsed;
        const portSuffix = port ? `:${port}` : '';

        if (hostname.startsWith('www.')) {
            origins.add(`${protocol}//${hostname.slice(4)}${portSuffix}`);
        } else {
            origins.add(`${protocol}//www.${hostname}${portSuffix}`);
        }
    } catch {
        origins.add(url.replace(/\/$/, ''));
    }

    return origins;
}

const allowedOrigins = [
    ...new Set([
        ...originVariants(APP_URL),
        ...originVariants(SOCKET_SERVER_URL),
        ...(isProduction ? [] : devOrigins),
    ]),
];

function corsOrigin(origin, callback) {
    if (!origin || allowedOrigins.includes(origin)) {
        callback(null, true);
        return;
    }

    callback(new Error(`CORS blocked: ${origin}`));
}

const HOST = process.env.SOCKET_HOST || (isProduction ? '0.0.0.0' : '127.0.0.1');

function createNodeServer(app) {
    if (useTls()) {
        return createHttpsServer(
            {
                key: fs.readFileSync(SSL_KEY_PATH),
                cert: fs.readFileSync(SSL_CERT_PATH),
            },
            app,
        );
    }

    return createHttpServer(app);
}

function publicUrl() {
    if (SOCKET_SERVER_URL) {
        return SOCKET_SERVER_URL.replace(/\/$/, '');
    }

    const scheme = useTls() ? 'https' : 'http';
    const displayHost = HOST === '0.0.0.0' ? 'localhost' : HOST;

    return `${scheme}://${displayHost}:${PORT}`;
}

function verifyToken(token) {
    if (!token || !SECRET) {
        return null;
    }

    const parts = String(token).split('.');
    if (parts.length !== 2) {
        return null;
    }

    const [encoded, signature] = parts;
    const expected = crypto.createHmac('sha256', SECRET).update(encoded).digest('hex');

    if (signature.length !== expected.length) {
        return null;
    }

    if (!crypto.timingSafeEqual(Buffer.from(expected, 'utf8'), Buffer.from(signature, 'utf8'))) {
        return null;
    }

    try {
        const json = Buffer.from(encoded.replace(/-/g, '+').replace(/_/g, '/'), 'base64').toString('utf8');
        const payload = JSON.parse(json);
        if (!payload?.conversation_id || !payload?.exp || payload.exp < Math.floor(Date.now() / 1000)) {
            return null;
        }

        return payload;
    } catch {
        return null;
    }
}

function handleBroadcast(req, res, io) {
    const headerSecret = req.headers['x-socket-secret'];
    if (!SECRET || headerSecret !== SECRET) {
        return res.status(403).json({ ok: false, error: 'Forbidden' });
    }

    const conversationId = req.body?.conversation_id;
    const message = req.body?.message;

    if (!conversationId || !message?.id) {
        return res.status(422).json({ ok: false, error: 'Invalid payload' });
    }

    io.to(`conversation:${conversationId}`).emit('new-message', message);

    return res.json({ ok: true });
}

const app = express();
app.use(cors({ origin: corsOrigin, credentials: true }));
app.use(express.json({ limit: '32kb' }));

const httpServer = createNodeServer(app);
const io = new Server(httpServer, {
    cors: {
        origin: corsOrigin,
        credentials: true,
    },
    connectionStateRecovery: {
        maxDisconnectionDuration: 120000,
        skipMiddlewares: true,
    },
    pingTimeout: 60000,
    pingInterval: 25000,
});

io.use((socket, next) => {
    const token = socket.handshake.auth?.token;
    if (!token) {
        next(new Error('Unauthorized'));
        return;
    }

    const payload = verifyToken(token);
    if (!payload) {
        console.warn('Socket auth failed: token invalid or expired (check SOCKET_SERVER_SECRET matches Laravel)');
        next(new Error('Unauthorized'));
        return;
    }

    socket.data.authPayload = payload;
    next();
});

io.on('connection', (socket) => {
    const payload = socket.data.authPayload;
    if (!payload) {
        return;
    }

    const room = `conversation:${payload.conversation_id}`;
    socket.join(room);
    socket.data.conversationId = payload.conversation_id;
    socket.data.role = payload.role;

    socket.on('disconnect', () => {});
});

app.post('/internal/broadcast', (req, res) => handleBroadcast(req, res, io));

app.get('/health', (_req, res) => {
    res.json({ ok: true, service: 'rentkia-socket' });
});

httpServer.listen(PORT, HOST, () => {
    const scheme = useTls() ? 'https' : 'http';
    console.log(`Socket.IO [${process.env.APP_ENV || 'local'}] ${scheme}://${HOST}:${PORT}`);
    console.log(`Public URL: ${publicUrl()}`);
});

if (isProduction) {
    const broadcastApp = express();
    broadcastApp.use(express.json({ limit: '32kb' }));
    broadcastApp.post('/internal/broadcast', (req, res) => handleBroadcast(req, res, io));

    createHttpServer(broadcastApp).listen(BROADCAST_PORT, '127.0.0.1', () => {
        console.log(`Broadcast (internal) http://127.0.0.1:${BROADCAST_PORT}/internal/broadcast`);
    });
}
