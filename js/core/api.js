const API_BASE = 'http://localhost:8000/api';

async function apiPost(endpoint, data) {
    const headers = { 'Content-Type': 'application/json' };

    const response = await fetch(`${API_BASE}${endpoint}`, {
        method: 'POST',
        headers,
        body: JSON.stringify(data),
    });

    return response.json();
}

async function apiGet(endpoint) {
    const response = await fetch(`${API_BASE}${endpoint}`, {
        headers: { 'Content-Type': 'application/json' },
    });
    return response.json();
}