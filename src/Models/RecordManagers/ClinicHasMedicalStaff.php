<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Models\RecordManagers;

use Hubleto\App\Community\Auth\Models\RecordManagers\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicHasMedicalStaff extends \Hubleto\Erp\RecordManager
{
  public $table = 'hlthcr_clinic_has_medical_staff';

  public function CLINIC(): BelongsTo
  {
    return $this->belongsTo(Clinic::class, 'id_clinic', 'id');
  }

  public function USER(): BelongsTo
  {
    return $this->belongsTo(User::class, 'id_user', 'id');
  }

  public function prepareReadQuery(mixed $query = null, int $level = 0, array|null $includeRelations = null): mixed
  {
    return parent::prepareReadQuery($query, $level, $includeRelations);
  }

  // Clinic form tab "Zdravotný personál".
  public function addUrlFiltersToQuery(mixed $query): mixed
  {
    $query = parent::addUrlFiltersToQuery($query);

    $idClinic = \Hubleto\Erp\Loader::getGlobalApp()->router()->urlParamAsInteger('idClinic');
    if ($idClinic > 0) {
      $query = $query->where($this->table . '.id_clinic', $idClinic);
    }

    return $query;
  }

  public function prepareLookupData(array $dataRaw): array
  {
    return parent::prepareLookupData($dataRaw);
  }
}
