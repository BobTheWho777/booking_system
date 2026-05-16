<?php
declare(strict_types=1);

function upload_room_images(PDO $pdo, int $roomId, array $files): void
{
    if (empty($files['name'][0])) {
        return;
    }

    if (!class_exists('finfo')) {
        rooms_redirect('error', 'Расширение fileinfo недоступно.');
    }

    $uploadDir = __DIR__ . '/../uploads/rooms/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $fileInfo = new finfo(FILEINFO_MIME_TYPE);

    foreach ($files['tmp_name'] as $key => $tmpName) {
        $error = $files['error'][$key];

        if ($error !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'Файл слишком большой (превышен лимит php.ini)',
                UPLOAD_ERR_FORM_SIZE => 'Файл слишком большой (превышен лимит формы)',
                UPLOAD_ERR_PARTIAL => 'Файл загружен частично',
                UPLOAD_ERR_NO_FILE => 'Файл не был загружен',
                UPLOAD_ERR_NO_TMP_DIR => 'Отсутствует временная директория',
                UPLOAD_ERR_CANT_WRITE => 'Ошибка записи на диск',
                UPLOAD_ERR_EXTENSION => 'Загрузка прервана расширением PHP'
            ];

            $errorMsg = $errorMessages[$error] ?? 'Неизвестная ошибка';
            throw new RuntimeException('Ошибка загрузки файла: ' . $errorMsg);
        }

        $mimeType = $fileInfo->file($tmpName);

        if (!in_array($mimeType, $allowedMimeTypes, true)) {
            throw new RuntimeException('Недопустимый тип файла. Разрешены только JPG, PNG, GIF, WebP.');
        }

        $fileName = uniqid('', true) . '_' . preg_replace(
            '/[^a-zA-Z0-9._-]/',
            '',
            basename($files['name'][$key])
        );

        $targetPath = $uploadDir . $fileName;

        if (!move_uploaded_file($tmpName, $targetPath)) {
            throw new RuntimeException('Ошибка при сохранении файла: ' . $fileName);
        }

        chmod($targetPath, 0644);

        $stmt = $pdo->prepare(
            'INSERT INTO room_images (room_id, image_path, sort_order) VALUES (?, ?, ?)'
        );

        $stmt->execute([$roomId, $fileName, $key]);
    }
}

function delete_room_images(array $images): void
{
    $uploadsDir = realpath(__DIR__ . '/../uploads/rooms');

    if ($uploadsDir === false) {
        return;
    }

    foreach ($images as $img) {
        $filePath = realpath(__DIR__ . '/../uploads/rooms/' . $img);

        if ($filePath !== false && str_starts_with($filePath, $uploadsDir)) {
            unlink($filePath);
        }
    }
}
