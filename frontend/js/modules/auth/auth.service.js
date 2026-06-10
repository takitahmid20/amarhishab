async function signup(name, email, password, confirmPassword) {
    console.log('Signup request', { name, email });
    const data = await apiPost('/signup', {
        name,
        email,
        password,
        password_confirmation: confirmPassword,
    });
    console.log('Signup response', data);

    if (data && data.success) {
        localStorage.setItem('auth_token', data.token);
        localStorage.setItem('user', JSON.stringify(data.user));
        window.location.href = './dashboard.html';
        return null;
    }

    return data?.message || 'Signup failed';
}

async function login(email, password) {
    console.log('Login request', { email });
    const data = await apiPost('/login', { email, password });
    console.log('Login response', data);

    if (data && data.success) {
        localStorage.setItem('auth_token', data.token);
        localStorage.setItem('user', JSON.stringify(data.user));
        window.location.href = './dashboard.html';
        return null;
    }

    return data?.message || 'Login failed';
}

async function logout() {
    await apiPost('/logout', {});
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');
    window.location.href = './login.html';
}