<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Services;

/**
 * Sends an uploaded file to the browser. The upload folder itself is not public (it will hold medical scans),
 * so controllers check the user's access first and then call send().
 */
class ProtectedFile extends \Hubleto\Erp\Core
{
  public string $translationContext = 'hubleto-app-external-waiblue-hlthcrcore-loader';
  public string $translationContextInner = 'Services\\ProtectedFile';

  /**
   * Sets download headers and returns the file content, or a message when the file is missing.
   * $relativePath is the value of a File column (relative to the upload folder).
   */
  public function send(string $relativePath): string
  {
    $uploadFolder = realpath($this->config()->getAsString('uploadFolder'));
    $filePath = realpath($uploadFolder . '/' . $relativePath);

    // realpath() resolves "..", so this also stops paths that point outside the upload folder.
    $isFileInUploadFolder = $uploadFolder !== false
      && $filePath !== false
      && str_starts_with($filePath, $uploadFolder . DIRECTORY_SEPARATOR)
      && is_file($filePath);

    if (!$isFileInUploadFolder) {
      return $this->refuse(404, $this->translate('The file was not found.'));
    }

    $contentType = mime_content_type($filePath);
    if ($contentType === false) $contentType = 'application/octet-stream';

    header('Content-Type: ' . $contentType);
    header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
    header('Content-Length: ' . filesize($filePath));
    header('Cache-Control: private, no-store');

    return (string) file_get_contents($filePath);
  }

  // Plain-text answer when the user may not download the file or it does not exist.
  public function refuse(int $httpStatus, string $message): string
  {
    http_response_code($httpStatus);
    header('Content-Type: text/plain; charset=utf-8');
    return $message;
  }
}
