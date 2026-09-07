const WebSocket = require('ws');

const appKey = 'wbrlefqkvazl8xbdli6q';

const channelName = 'private-conference.19';

// ضعي هنا Sanctum Token جديد للمستخدم المقبول في المؤتمر
const token = '52|274Jbc3w9aaChhTj7NB7ZiB8FSvGLrFmLxvKyEDBafce129e';

// الاتصال بـ Reverb عبر Cloudflare Tunnel
const ws = new WebSocket(
    `wss://gui-summaries-denied-wilson.trycloudflare.com/app/${appKey}`
);

// ==========================================
// WEBSOCKET CONNECTED
// ==========================================

ws.on('open', () => {
    console.log('✅ WebSocket connected to Reverb');
});

// ==========================================
// RECEIVE REVERB EVENTS
// ==========================================

ws.on('message', async(data) => {

    try {

        const message = JSON.parse(data.toString());

        console.log('📩 Reverb event:');
        console.log(message);

        // ==========================================
        // PING / PONG
        // ==========================================

        if (message.event === 'pusher:ping') {

            ws.send(JSON.stringify({
                event: 'pusher:pong',
                data: {}
            }));

            console.log('🏓 Pong sent');

            return;
        }

        // ==========================================
        // CONNECTION ESTABLISHED
        // ==========================================

        if (message.event === 'pusher:connection_established') {

            const connectionData = JSON.parse(message.data);

            const socketId = connectionData.socket_id;

            console.log('🔑 Socket ID:', socketId);

            // ==========================================
            // BROADCASTING AUTH
            // ==========================================

            try {

                console.log('🔐 Requesting broadcasting auth...');

                const response = await fetch(
                    'http://127.0.0.1:8000/broadcasting/auth', {
                        method: 'POST',

                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },

                        body: JSON.stringify({
                            socket_id: socketId,
                            channel_name: channelName
                        })
                    }
                );

                const result = await response.json();

                console.log('🔐 Broadcasting Auth response:');
                console.log(result);

                // ==========================================
                // AUTH FAILED
                // ==========================================

                if (!response.ok) {

                    console.error(
                        '❌ Broadcasting auth failed'
                    );

                    return;
                }

                // ==========================================
                // SUBSCRIBE TO PRIVATE CHANNEL
                // ==========================================

                ws.send(JSON.stringify({
                    event: 'pusher:subscribe',

                    data: {
                        auth: result.auth,
                        channel: channelName
                    }
                }));

                console.log('📡 Subscribe request sent');

            } catch (error) {

                console.error(
                    '❌ Error while requesting broadcasting auth:',
                    error.message
                );
            }
        }

        // ==========================================
        // SUBSCRIPTION SUCCESS
        // ==========================================

        if (
            message.event ===
            'pusher_internal:subscription_succeeded'
        ) {

            console.log(
                '🎉 Private Channel subscription succeeded!'
            );
        }

        if (
            message.event ===
            'pusher:subscription_succeeded'
        ) {

            console.log(
                '🎉 Private Channel subscription succeeded!'
            );
        }

        // ==========================================
        // MESSAGE SENT EVENT
        // ==========================================

        if (message.event === 'message.sent') {

            console.log('💬 Message received:');

            console.log(
                JSON.stringify(
                    message.data,
                    null,
                    2
                )
            );
        }

        // ==========================================
        // CONFERENCE ENDED EVENT
        // ==========================================

        if (message.event === 'conference.ended') {

            console.log('🔴 Conference ended:');

            console.log(
                JSON.stringify(
                    message.data,
                    null,
                    2
                )
            );
        }

        // ==========================================
        // WEBRTC SIGNAL
        // ==========================================

        if (message.event === 'webrtc.signal') {

            console.log('📡 WebRTC signal received:');

            console.log(
                JSON.stringify(
                    message.data,
                    null,
                    2
                )
            );
        }

    } catch (error) {

        console.error(
            '❌ Error processing Reverb message:',
            error.message
        );
    }
});

// ==========================================
// WEBSOCKET ERROR
// ==========================================

ws.on('error', (error) => {

    console.error(
        '❌ WebSocket error:',
        error.message
    );
});

// ==========================================
// WEBSOCKET CLOSED
// ==========================================

ws.on('close', (code, reason) => {

    console.log('🔴 WebSocket closed');

    console.log('Code:', code);

    console.log(
        'Reason:',
        reason.toString()
    );
});