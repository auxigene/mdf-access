<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Téléchargement - {{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            body {
                font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            .container {
                background: white;
                border-radius: 16px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                max-width: 600px;
                width: 100%;
                padding: 48px 40px;
                text-align: center;
            }
            .icon-container {
                margin-bottom: 24px;
            }
            .icon {
                display: inline-block;
                width: 80px;
                height: 80px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto;
            }
            .icon svg {
                width: 40px;
                height: 40px;
                stroke: white;
            }
            h1 {
                font-size: 32px;
                font-weight: 600;
                color: #1a202c;
                margin-bottom: 12px;
            }
            p {
                font-size: 16px;
                color: #718096;
                margin-bottom: 32px;
                line-height: 1.6;
            }
            .file-list {
                background: #f7fafc;
                border-radius: 8px;
                padding: 24px;
                margin-bottom: 32px;
            }
            .file-item {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 16px;
                background: white;
                border-radius: 8px;
                margin-bottom: 12px;
                transition: all 0.3s ease;
                border: 2px solid transparent;
            }
            .file-item:hover {
                border-color: #667eea;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
            }
            .file-item:last-child {
                margin-bottom: 0;
            }
            .file-info {
                display: flex;
                align-items: center;
                gap: 12px;
            }
            .file-icon {
                width: 40px;
                height: 40px;
                background: #e6fffa;
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .file-icon svg {
                width: 24px;
                height: 24px;
                stroke: #38b2ac;
            }
            .file-details h3 {
                font-size: 16px;
                font-weight: 500;
                color: #2d3748;
                margin-bottom: 4px;
                text-align: left;
            }
            .file-details span {
                font-size: 14px;
                color: #a0aec0;
            }
            .btn {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 12px 24px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                text-decoration: none;
                border-radius: 8px;
                font-weight: 500;
                font-size: 14px;
                transition: all 0.3s ease;
                border: none;
                cursor: pointer;
            }
            .btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 16px rgba(102, 126, 234, 0.4);
            }
            .btn svg {
                width: 18px;
                height: 18px;
            }
            .home-link {
                display: inline-block;
                margin-top: 24px;
                color: #667eea;
                text-decoration: none;
                font-size: 14px;
                font-weight: 500;
                transition: color 0.3s ease;
            }
            .home-link:hover {
                color: #764ba2;
            }
            @media (max-width: 640px) {
                .container {
                    padding: 32px 24px;
                }
                h1 {
                    font-size: 24px;
                }
                .file-item {
                    flex-direction: column;
                    gap: 16px;
                }
                .file-info {
                    width: 100%;
                }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="icon-container">
                <div class="icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                    </svg>
                </div>
            </div>

            <h1>Téléchargement de fichiers</h1>
            <p>Téléchargez les fichiers Excel mis à jour avec les dernières données de votre application.</p>

            <div class="file-list">
                <div class="file-item">
                    <div class="file-info">
                        <div class="file-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="file-details">
                            <h3>template.xlsx</h3>
                            <span>Fichier Excel principal</span>
                        </div>
                    </div>
                    <a href="{{ route('excel.download', ['fileName' => 'template.xlsx']) }}" class="btn" download>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Télécharger
                    </a>
                </div>
            </div>

            <a href="/" class="home-link">← Retour à l'accueil</a>
        </div>
    </body>
</html>
