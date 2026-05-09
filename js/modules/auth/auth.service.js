async function signup(name, email, password, confirmPassword) {
    const data = await apiPost('/signup', {
        name,
        email,
        password,
        password_confirmation: confirmPassword,
    });

    if (data.success) {
        localStorage.setItem('auth_token', data.token);
        localStorage.setItem('user', JSON.stringify(data.user));
        window.location.href = './dashboard.html';
    } else {
        return data.message || 'Signup failed';
    }
}

async function login(email, password) {
    const data = await apiPost('/login', { email, password });

    if (data.success) {
        localStorage.setItem('auth_token', data.token);
        localStorage.setItem('user', JSON.stringify(data.user));
        window.location.href = './dashboard.html';
    } else {
        return data.message || 'Login failed';
    }
}

async function logout() {
    await apiPost('/logout', {});
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');
    window.location.href = './login.html';
}