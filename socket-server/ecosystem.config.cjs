/** PM2: run exactly ONE instance (cluster mode causes "Session ID unknown"). */
module.exports = {
    apps: [
        {
            name: 'rentkia-socket',
            script: 'server.js',
            cwd: __dirname,
            instances: 1,
            exec_mode: 'fork',
            autorestart: true,
            max_memory_restart: '256M',
        },
    ],
};
