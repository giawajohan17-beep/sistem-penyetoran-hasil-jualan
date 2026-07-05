<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PT Swakarsa Berjaya</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* --- PENGATURAN BACKGROUND FOTO --- */
        .bg-full {
            /* Menggunakan file foto.jpg sebagai latar belakang */
            background-image: url('foto.jpg'); 
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            
            /* Memberikan efek gelap agar form login lebih menonjol */
            filter: brightness(0.5); 
        }

        /* --- KOTAK LOGIN (STYLE MODERN) --- */
        .login-box { 
            background: rgba(255, 255, 255, 0.15); /* Transparan untuk efek kaca */
            backdrop-filter: blur(10px);          /* Efek blur pada kaca */
            -webkit-backdrop-filter: blur(10px);
            padding: 40px 30px; 
            border-radius: 20px; 
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
            width: 340px; 
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.18);
            color: white;
        }

        .logo-container img {
            width: 90px; 
            height: auto;
            margin-bottom: 15px;
            /* Memberikan sedikit bayangan pada logo agar tidak tenggelam */
            filter: drop-shadow(0px 4px 4px rgba(0,0,0,0.2));
        }

        h2 { 
            margin: 0 0 25px 0;
            color: #ffffff; 
            font-size: 1.6em;
            font-weight: 700;
            letter-spacing: 2px;
            text-shadow: 0px 2px 4px rgba(0,0,0,0.3);
        }

        input { 
            width: 100%; 
            padding: 14px; 
            margin: 12px 0; 
            border: none; 
            border-radius: 12px; 
            outline: none;
            background: rgba(255, 255, 255, 0.9);
            color: #333;
            font-size: 1em;
            transition: 0.3s;
        }

        input:focus {
            background: #ffffff;
            box-shadow: 0 0 15px rgba(230, 126, 34, 0.4);
        }

        button { 
            width: 100%; 
            padding: 14px; 
            background: #e67e22; 
            border: none; 
            color: white; 
            border-radius: 12px; 
            cursor: pointer; 
            font-weight: bold;
            font-size: 1.1em;
            margin-top: 20px;
            transition: 0.3s;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        button:hover { 
            background: #d35400; 
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(230, 126, 34, 0.4);
        }

        /* Responsive untuk HP */
        @media (max-width: 400px) {
            .login-box {
                width: 90%;
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>

    <div class="bg-full"></div>

    <div class="login-box">
        <div class="logo-container">
            <img src="logo.png" alt="Logo PT Swakarsa">
        </div>

        <h2>SWAKARSA APP</h2>
        
        <form action="proses_login.php" method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">MASUK</button>
        </form>
        
        <p style="margin-top: 20px; font-size: 0.8em; opacity: 0.7;">
            &copy; 2026 PT Swakarsa Berjaya Indonesia
        </p>
    </div>

</body>
</html>