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
dotenv.config({ path: path.resolve(__dirname, '.env') });

const isProduction = process.env.APP_ENV === 'production';
const PORT = Number(process.env.SOCKET_PORT || 6001);
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

const HOST =
    process.env.SOCKET_HOST || (isProduction && useTls() ? '0.0.0.0' : '127.0.0.1');

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

const app = express();
app.use(cors({ origin: allowedOrigins, credentials: true }));
app.use(express.json({ limit: '32kb' }));

const httpServer = createNodeServer(app);
const io = new Server(httpServer, {
    cors: {
        origin: allowedOrigins,
        credentials: true,
    },
});

io.on('connection', (socket) => {
    const payload = verifyToken(socket.handshake.auth?.token);
    if (!payload) {
        socket.disconnect(true);
        return;
    }

    const room = `conversation:${payload.conversation_id}`;
    socket.join(room);
    socket.data.conversationId = payload.conversation_id;
    socket.data.role = payload.role;

    socket.on('disconnect', () => {});
});

app.post('/internal/broadcast', (req, res) => {
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
});

app.get('/health', (_req, res) => {
    res.json({ ok: true, service: 'rentkia-socket' });
});

httpServer.listen(PORT, HOST, () => {
    const scheme = useTls() ? 'https' : 'http';
    console.log(`Socket.IO server [${process.env.APP_ENV || 'local'}] ${scheme}://${HOST}:${PORT}`);
    console.log(`Public URL: ${publicUrl()}`);
});
