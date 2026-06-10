const API_BASE = 'http://localhost/amarhishab/backend/index.php/api';

function buildHeaders() {
    const headers = { 'Content-Type': 'application/json' };
    const token = localStorage.getItem('auth_token');
    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }
    return headers;
}

async function apiPost(endpoint, data) {
    try {
        const response = await fetch(`${API_BASE}${endpoint}`, {
            method: 'POST',
            headers: buildHeaders(),
            body: JSON.stringify(data),
        });

        const json = await response.json();
        if (!response.ok) {
            return {
                success: false,
                message: json.message || `Server error: ${response.status}`,
            };
        }

        return json;
    } catch (error) {
        return {
            success: false,
            message: 'Unable to connect to backend. Start the PHP server at http://localhost:8000 and try again.',
        };
    }
}

async function apiGet(endpoint) {
    const response = await fetch(`${API_BASE}${endpoint}`, {
        headers: { 'Content-Type': 'application/json' },
    });
    return response.json();
}