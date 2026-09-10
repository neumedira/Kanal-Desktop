<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Kanal Kalimantan</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }
    </style>
</head>
<body class="bg-[#ececec] min-h-screen flex items-center justify-center p-4">

    <!-- Card Container -->
    <div class="bg-white rounded-lg shadow-xl w-full max-w-[420px] p-8 md:p-10 text-center">
        
        <!-- Logo & Title -->
        <div class="flex flex-col items-center mb-8">
            <img src="{{ asset('images/logo.png') }}" alt="Kanal Kalimantan" class="h-16 w-auto mb-2 object-contain">
            <h1 class="font-bold text-gray-900 text-lg tracking-tight">Kanal Kalimantan</h1>
        </div>

        <!-- Form Login -->
        <form id="loginForm" class="space-y-5 text-left">
            @csrf

            <!-- Username Field -->
            <div>
                <label for="username" class="block text-xs font-semibold text-gray-500 mb-1.5">
                    Username / Email
                </label>
                <div class="relative flex items-center">
                    <span class="absolute left-3 text-gray-400 text-sm">
                        <i class="fa-regular fa-user"></i>
                    </span>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        placeholder="Masukkan username atau email" 
                        required 
                        autofocus
                        class="w-full pl-9 pr-3 py-2.5 text-sm bg-white border border-gray-300 rounded focus:outline-none focus:border-black text-gray-700 placeholder-gray-400 transition"
                    >
                </div>
            </div>

            <!-- Password Field -->
            <div>
                <div class="flex justify-between items-center mb-1.5">
                    <label for="password" class="block text-xs font-semibold text-gray-500">
                        Password
                    </label>
                    <a href="#" class="text-xs font-bold text-gray-900 hover:underline">
                        Lupa Password?
                    </a>
                </div>
                <div class="relative flex items-center">
                    <span class="absolute left-3 text-gray-400 text-sm">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Masukkan password" 
                        required 
                        class="w-full pl-9 pr-3 py-2.5 text-sm bg-white border border-gray-300 rounded focus:outline-none focus:border-black text-gray-700 placeholder-gray-400 transition"
                    >
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <button 
                    type="submit" 
                    id="submitBtn"
                    class="w-full bg-black hover:bg-gray-800 text-white font-bold py-2.5 rounded text-sm tracking-wide transition duration-150 flex items-center justify-center"
                >
                    <span id="btnText">Masuk</span>
                </button>
            </div>
        </form>

    </div>

    <!-- Script Integrasi API Backend -->
   <script>
    document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const usernameInput = document.getElementById('username').value;
        const passwordInput = document.getElementById('password').value;

        // Loading state
        submitBtn.disabled = true;
        btnText.innerText = 'Memproses...';

        try {
            const response = await fetch('/api/v1/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    username: usernameInput,
                    password: passwordInput
                })
            });

            const data = await response.json();

            if (response.ok && data.status === 'success') {
                // Simpan Token & Data User ke Browser
                localStorage.setItem('access_token', data.access_token);
                localStorage.setItem('user_data', JSON.stringify(data.user));
                
                // Redirect ke Dashboard
                window.location.href = '/dashboard';
            } else {
                alert('Login Gagal: ' + (data.message || 'Username atau password salah!'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan koneksi ke server API.');
        } finally {
            // Reset loading state
            submitBtn.disabled = false;
            btnText.innerText = 'Masuk';
        }
    });
</script>

</body>
</html>