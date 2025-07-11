<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>WHM Server Manager</title>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: "Segoe UI", sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #fff;
        }

        .container {
            width: 100%;
            max-width: 400px;
            background: #fff;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
            color: #021526;
        }

        h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: #021526;
            font-weight: 700;
        }

        input,
        select,
        button {
            width: 100%;
            padding: 0.75rem;
            margin-bottom: 1rem;
            border: 1px solid #021526;
            border-radius: 8px;
            font-size: 1rem;
            color: #021526;
            background: #f9fafb;
            transition: background 0.3s, border-color 0.3s;
        }

        input:focus,
        select:focus {
            outline: none;
            background: #e0f2fe;
            border-color: #0284c7;
        }

        button {
            background-color: #021526;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
        }

        button:hover {
            background-color: #034078;
        }

        .hidden {
            display: none;
        }
    </style>
</head>

<body>

    <div class="container" id="login-box">
        <h2>Login</h2>
        <input type="email" id="email" placeholder="Email" autocomplete="username" required />
        <input type="password" id="password" placeholder="Password" autocomplete="current-password" required />
        <button id="login-button">Login</button>
    </div>

    <div class="container hidden" id="add-server-box" aria-hidden="true">
        <h2>Add Server</h2>
        <input type="text" id="name" placeholder="Server Name" maxlength="255" required />
        <input type="text" id="hostname" placeholder="Hostname (e.g. whm1.example.com)" maxlength="255" required />
        <input type="text" id="whm_user" placeholder="WHM Username" maxlength="255" required />
        <input type="text" id="token" placeholder="WHM Token" maxlength="500" required />
        <select id="is_active" aria-label="Server Status">
            <option value="1" selected>Active</option>
            <option value="0">Inactive</option>
        </select>
        <button id="add-button">Add Server</button>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            checkLogin();
        });

        async function checkLogin() {
            try {
                const res = await fetch('/api/check-auth', {
                    method: 'GET',
                    credentials: 'include'
                });
                if (res.ok) {
                    showAddServer();
                } else {
                    showLogin();
                }
            } catch {
                showLogin();
            }
        }

        function showLogin() {
            document.getElementById('login-box').classList.remove('hidden');
            document.getElementById('add-server-box').classList.add('hidden');
            document.getElementById('add-server-box').setAttribute('aria-hidden', 'true');
        }

        function showAddServer() {
            document.getElementById('login-box').classList.add('hidden');
            document.getElementById('add-server-box').classList.remove('hidden');
            document.getElementById('add-server-box').setAttribute('aria-hidden', 'false');
        }

        document.getElementById('login-button').addEventListener('click', async () => {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value.trim();

            if (!email || !password) {
                return swal("Error", "Please enter email and password.", "error");
            }

            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email)) {
                return swal("Error", "Invalid email format.", "error");
            }

            try {
                // الحصول على CSRF cookie
                await fetch('/sanctum/csrf-cookie', {
                    credentials: 'include'
                });

                const res = await fetch('/api/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        email,
                        password
                    }),
                    credentials: 'include'
                });

                const data = await res.json();

                if (res.ok) {
                    swal("Success", "Login successful!", "success");
                    showAddServer();
                } else {
                    swal("Error", data.message || "Login failed.", "error");
                }
            } catch (err) {
                console.error(err);
                swal("Error", "Server error. Please try again later.", "error");
            }
        });

        document.getElementById('add-button').addEventListener('click', async () => {
            const name = document.getElementById('name').value.trim();
            const hostname = document.getElementById('hostname').value.trim();
            const whmUser = document.getElementById('whm_user').value.trim();
            const whmToken = document.getElementById('token').value.trim();
            const isActive = document.getElementById('is_active').value;

            if (!name || !hostname || !whmUser || !whmToken) {
                return swal("Error", "Please fill all fields.", "error");
            }

            const hostnamePattern = /^(([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,})$/;
            if (!hostnamePattern.test(hostname)) {
                return swal("Error", "Invalid hostname format.", "error");
            }

            if (/[<>"'`;&]/.test(whmToken)) {
                return swal("Error", "Invalid characters in token.", "error");
            }

            try {
                // الحصول على CSRF cookie
                await fetch('/sanctum/csrf-cookie', {
                    credentials: 'include'
                });

                const res = await fetch('/api/server', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    credentials: 'include',
                    body: JSON.stringify({
                        name,
                        hostname,
                        user: whmUser,
                        token: whmToken,
                        is_active: isActive
                    })
                });

                const data = await res.json();

                if (res.ok) {
                    swal("Success", "Server added successfully!", "success");
                    ['name', 'hostname', 'whm_user', 'token'].forEach(id => document.getElementById(id).value =
                        '');
                } else {
                    swal("Error", data.message || "Failed to add server.", "error");
                }
            } catch (err) {
                console.error(err);
                swal("Error", "Network error. Please try again.", "error");
            }
        });
    </script>

</body>

</html>
