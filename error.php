<?php
// Daftar pesan error berdasarkan kodenya
$error_codes = [
    '403' => [
        'title' => 'Akses Dilarang',
        'message' => 'Maaf, Anda tidak memiliki izin untuk mengakses halaman ini. Silakan kembali ke beranda.',
    ],
    '404' => [
        'title' => 'Halaman Tidak Ditemukan',
        'message' => 'Oops! Halaman yang Anda cari sepertinya tidak ada atau sudah dipindahkan. Mari kami bantu Anda kembali ke jalan yang benar.',
    ],
    '500' => [
        'title' => 'Server Bermasalah',
        'message' => 'Sepertinya terjadi sedikit masalah di server kami. Tim kami sudah mengetahuinya dan sedang bekerja untuk memperbaikinya.',
    ],
    'default' => [
        'title' => 'Terjadi Kesalahan',
        'message' => 'Telah terjadi kesalahan yang tidak terduga. Mohon coba lagi nanti atau kembali ke beranda.',
    ]
];

// Ambil kode error dari URL, default ke 'default' jika tidak ada
$code = isset($_GET['code']) && array_key_exists($_GET['code'], $error_codes) ? $_GET['code'] : 'default';
$error = $error_codes[$code];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($code . ' - ' . $error['title']); ?> | Lazismu</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #FF7B00;
            --primary-dark: #E06D00;
            --secondary: #FFE0C2;
            --accent: #FFA347;
            --dark: #1F1F1F;
            --light: #FFFFFF;
            --gray: #6c757d;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #FFF5EB 0%, #FFEDDC 100%);
            color: var(--dark);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 1rem;
            position: relative;
            overflow: hidden;
        }
        
        /* Background shapes */
        body::before {
            content: "";
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(255, 167, 61, 0.1);
            top: -150px;
            left: -150px;
            z-index: -1;
        }
        
        body::after {
            content: "";
            position: absolute;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(255, 167, 61, 0.08);
            bottom: -100px;
            right: -100px;
            z-index: -1;
        }
        
        .error-container {
            text-align: center;
            max-width: 650px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
        
        .error-container::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
        }
        
        .error-code {
            font-size: 8rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1;
            margin-bottom: 1.5rem;
            text-shadow: 0 5px 15px rgba(255, 123, 0, 0.2);
            position: relative;
            display: inline-block;
        }
        
        .error-code::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            border-radius: 2px;
        }
        
        .error-title {
            font-size: 2.2rem;
            font-weight: 700;
            margin-top: 1.5rem;
            color: var(--dark);
        }
        
        .error-message {
            font-size: 1.15rem;
            color: var(--gray);
            margin-top: 1.2rem;
            margin-bottom: 2.5rem;
            line-height: 1.6;
        }
        
        .btn-lazismu {
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            border: none;
            color: var(--light);
            padding: 0.9rem 2rem;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            box-shadow: 0 5px 15px rgba(255, 123, 0, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .btn-lazismu:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(255, 123, 0, 0.4);
            color: var(--light);
        }
        
        .btn-lazismu:active {
            transform: translateY(1px);
        }
        
        .social-container {
            margin-top: 2.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .social-text {
            color: var(--gray);
            margin-bottom: 1rem;
            font-size: 1rem;
        }
        
        .social-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.7rem 1.5rem;
            background: var(--secondary);
            color: var(--primary-dark);
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(255, 123, 0, 0.1);
        }
        
        .social-link:hover {
            background: var(--primary);
            color: var(--light);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 123, 0, 0.2);
        }
        
        .social-link i {
            margin-right: 0.5rem;
            font-size: 1.2rem;
        }
        
        /* Animation for elements */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .error-code {
            animation: fadeIn 0.8s ease-out;
        }
        
        .error-title {
            animation: fadeIn 0.8s ease-out 0.2s both;
        }
        
        .error-message {
            animation: fadeIn 0.8s ease-out 0.4s both;
        }
        
        .btn-lazismu {
            animation: fadeIn 0.8s ease-out 0.6s both;
        }
        
        .social-container {
            animation: fadeIn 0.8s ease-out 0.8s both;
        }
        
        /* Responsive adjustments */
        @media (max-width: 576px) {
            .error-container {
                padding: 2rem 1.5rem;
            }
            
            .error-code {
                font-size: 5rem;
            }
            
            .error-title {
                font-size: 1.8rem;
            }
            
            .error-message {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>

    <div class="error-container">
        <div class="error-code"><?php echo htmlspecialchars($code); ?></div>
        <h1 class="error-title"><?php echo htmlspecialchars($error['title']); ?></h1>
        <p class="error-message">
            <?php echo htmlspecialchars($error['message']); ?>
        </p>
        <a href="/" class="btn btn-lazismu">Kembali ke Beranda</a>
        
        <div class="social-container">
            <p class="social-text">Atau kunjungi media sosial kami</p>
            <a href="https://www.instagram.com/lazismu.tulungagung" target="_blank" rel="noopener noreferrer" class="social-link">
                <i class="bi bi-instagram"></i> Instagram
            </a>
        </div>
    </div>

</body>
</html>