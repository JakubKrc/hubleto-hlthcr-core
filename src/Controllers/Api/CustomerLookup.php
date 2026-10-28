<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Controllers\Api;

use Hubleto\App\Community\Customers\Models\Customer;
use Hubleto\App\External\WaiBlue\HlthcrCore\Services\AccessScope;

/**
 * Client (Customer) dropdown limited to visible clients, also when HlthcrClients (which scopes the Customer
 * model itself) is not installed. Same output format as api/record/lookup.
 */
class CustomerLookup extends \Hubleto\Erp\Controllers\ApiController
{
  const ENDPOINT = 'hlthcr/api/customer-lookup';

  public function renderJson(): array
  {
    $search = $this->router()->urlParamAsString('search');
    /** @var Customer */
    $mCustomer = $this->getModel(Customer::class);

    $query = $mCustomer->record->prepareLookupQuery($search);
    $query = $this->getService(AccessScope::class)->restrictToLinkableCustomers($query, 'customers.id');
    if (method_exists($mCustomer->record, 'restrictToOfferedRecords')) {
      $query = $mCustomer->record->restrictToOfferedRecords($query);
    }

    $customers = $mCustomer->record->prepareLookupData($query->get()->toArray());
    // A list, as Hubleto's own lookup returns since Hubleto 2.
    return $customers;
  }
}
