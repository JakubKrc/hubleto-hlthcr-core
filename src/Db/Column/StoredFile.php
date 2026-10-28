<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Db\Column;

/**
 * File column that renames only new uploads.
 * Hubleto's File column applies the rename pattern also to the already saved path that the form sends back
 * on every save, so the record would point to a file that does not exist.
 */
class StoredFile extends \Hubleto\Framework\Db\Column\File
{
  // The form links the file name to this checked download route ({%ID%} = record id) instead of the upload folder.
  public function setDownloadUrl(string $downloadUrl): StoredFile
  {
    $this->setReactComponent('HlthcrCoreProtectedFileInput');
    $this->setProperty('downloadUrl', $downloadUrl);
    return $this;
  }

  public function normalize(mixed $value): mixed
  {
    $isNewUpload = is_array($value);
    if ($isNewUpload) return parent::normalize($value);

    // An already saved path; the model makes sure it is the record's own (see KeepsStoredFiles).
    return $value;
  }
}
