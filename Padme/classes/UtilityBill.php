<?php
/**
 * @author Nelson Long
 * @version 1.2
 * @date Ausust 22nd, 2025

 */

include_once __DIR__ . '/../include/functions.php';

class UtilityBill 
{
    public $ProviderID;
    public $ProviderName;
    public $ControlNumber;
    public $BillTotal;
    public $BillAccountNumber;
    public $IsNegative;
    public $MailingAddress;
    public $RemitAddress;
    public $ProhibitedElement;
    public $AccountInvoices = []; // Array of AccountInvoice objects

    public function __construct(array $d = []) 
    {
        $this->ProviderID        = pick($d, 'ProviderID');
        $this->ProviderName      = pick($d, 'ProviderName');
        $this->ControlNumber     = pick($d, 'ControlNumber');
        $this->BillTotal         = pick($d, 'BillTotal');
        $this->BillAccountNumber = pick($d, 'BillAccountNumber');
        $this->IsNegative        = pick($d, 'IsNegative');
        $this->MailingAddress    = pick($d, 'MailingAddress');
        $this->RemitAddress      = pick($d, 'RemitAddress');
        $this->ProhibitedElement = pick($d, 'ProhibitedElement');

        foreach (listify(pick($d, 'AccountInvoice', [])) as $row) {
            $this->AccountInvoices[] = new AccountInvoice($row);
        }
    }
    // --- inside class UtilityBill ---
public function toHtml(): string {
    $html  = '<section class="card utility-bill">';
    $html .= '<h4>Utility Bill</h4>';
    $html .= '<dl class="kv">';
    $html .= kv('Provider ID',        $this->ProviderID);
    $html .= kv('Provider Name',      $this->ProviderName);
    $html .= kv('Control Number',     $this->ControlNumber);
    //$html .= kv('Bill Total',         $this->BillTotal);
    //$html .= kv('Bill Account #',     $this->BillAccountNumber);
    //$html .= kv('Is Negative',        $this->IsNegative ? 'true' : 'false');
    $html .= kv('Mailing Address',    $this->MailingAddress);
    $html .= kv('Remit Address',      $this->RemitAddress);
    $html .= kv('Prohibited Element', $this->ProhibitedElement);
    $html .= '</dl>';

    if (!empty($this->AccountInvoices)) {
        $html .= '<div class="account-invoices">';
        foreach ($this->AccountInvoices as $i => $inv) {
            $html .= $inv->toHtml($i + 1);
        }
        $html .= '</div>';
    }
    $html .= '</section>';
    return $html;
}

}

class AccountInvoice extends UtilityBill
{
    public $AccountNumber;
    public $CustomerName;
    public $InvoiceNumber;
    public $InvoiceType;
    public $TotalDue;
    public $IsNegative;
    public $BillDate;
    public $DueDate;
    public $MailedDate;
    public $LateFeeDeadline;
    public $NextStatementDate;
    public $NextReadOn;
    public $ChargeGroup = []; // Array of ChargeGroup
    public $Address    = [];  // Array of Address
    
    public function __construct(array $d = []) 
    {
        $this->AccountNumber     = pick($d, 'AccountNumber');
        $this->CustomerName      = pick($d, 'CustomerName');
        $this->InvoiceNumber     = pick($d, 'InvoiceNumber');
        $this->InvoiceType       = pick($d, 'InvoiceType');
        $this->TotalDue          = pick($d, 'TotalDue');
        $this->IsNegative        = pick($d, 'IsNegative');
        $this->BillDate          = pick($d, 'BillDate');
        $this->DueDate           = pick($d, 'DueDate');
        $this->MailedDate        = pick($d, 'MailedDate');
        $this->LateFeeDeadline   = pick($d, 'LateFeeDeadline');
        $this->NextStatementDate = pick($d, 'NextStatementDate');
        $this->NextReadOn        = pick($d, 'NextReadOn');

        foreach (listify(pick($d, 'ChargeGroup', [])) as $row) {
            $this->ChargeGroup[] = new ChargeGroup($row);
        }
        foreach (listify(pick($d, 'Address', [])) as $row) {
            $this->Address[] = new Address($row);
        }
    }

    public function toHtml(int $index = null): string {
        $title = 'Account Invoice' . ($index ? " #$index" : '');
        $html  = '<section class="card account-invoice">';
        $html .= '<h5>'.h($title).'</h5>';
        $html .= '<dl class="kv">';
        $html .= kv('Account Number',     $this->AccountNumber);
        $html .= kv('Customer Name',      $this->CustomerName);
        $html .= kv('Invoice Number',     $this->InvoiceNumber);
        $html .= kv('Invoice Type',       $this->InvoiceType);
        $html .= kv('Total Due',          $this->TotalDue);
        $html .= kv('Is Negative',        $this->IsNegative ? 'true' : 'false');
        $html .= kv('Bill Date',          $this->BillDate);
        $html .= kv('Due Date',           $this->DueDate);
        $html .= kv('Mailed Date',        $this->MailedDate);
        $html .= kv('Late Fee Deadline',  $this->LateFeeDeadline);
        $html .= kv('Next Statement Date',$this->NextStatementDate);
        $html .= kv('Next Read On',       $this->NextReadOn);
        $html .= '</dl>';

    if (!empty($this->ChargeGroup)) {
        $html .= '<div class="charge-groups stack">';
        foreach ($this->ChargeGroup as $i => $cg) {
            $html .= $cg->toHtml($i + 1);
        }
        $html .= '</div>';
    }

    if (!empty($this->Address)) {
        $html .= '<div class="addresses stack">';
        foreach ($this->Address as $i => $addr) {
            $html .= $addr->toHtml($i + 1);
        }
        $html .= '</div>';
    }

    $html .= '</section>';
    return $html;
    }

}

class ChargeGroup extends AccountInvoice
{
    public $ServicePeriod;
    public $ServicePeriodText;
    public $ServiceStartAt;
    public $ServiceEndAt;
    public $DaysOfService;
    public $ServiceDate;
    public $TotalCharge;
    public $LineItemHeader = []; // Array of LineItemHeader
    public $LineItemCharge = []; // Array of LineItemCharge

    public function __construct(array $d = []) 
    {
        $this->ServicePeriod     = pick($d, 'ServicePeriod');
        $this->ServicePeriodText = pick($d, 'ServicePeriodText');
        $this->ServiceStartAt    = pick($d, 'ServiceStartAt');
        $this->ServiceEndAt      = pick($d, 'ServiceEndAt');
        $this->DaysOfService     = pick($d, 'DaysOfService');
        $this->ServiceDate       = pick($d, 'ServiceDate');
        $this->TotalCharge       = pick($d, 'TotalCharge');

        foreach (listify(pick($d, 'LineItemHeader', [])) as $row) {
            $this->LineItemHeader[] = new LineItemHeader($row);
        }
        foreach (listify(pick($d, 'LineItemCharge', [])) as $row) {
            $this->LineItemCharge[] = new LineItemCharge($row);
        }
    }

    // --- inside class ChargeGroup ---
public function toHtml(int $index = null): string {
    $title = 'Charge Group' . ($index ? " #$index" : '');
    $html  = '<section class="card charge-group">';
    $html .= '<h6>'.h($title).'</h6>';
    $html .= '<dl class="kv">';
    $html .= kv('Service Period',       $this->ServicePeriod);
    //$html .= kv('Service Period Text',  $this->ServicePeriodText);
    $html .= kv('Service Start At',     $this->ServiceStartAt);
    $html .= kv('Service End At',       $this->ServiceEndAt);
    $html .= kv('Days Of Service',      $this->DaysOfService);
    $html .= kv('Service Date',         $this->ServiceDate);
    $html .= kv('Total Charge',         $this->TotalCharge);
    $html .= '</dl>';

    /* LineItemCharge (rows)
    if (!empty($this->LineItemCharge)) {
        $html .= '<div class="lineitem-charges stack">';
        foreach ($this->LineItemCharge as $i => $lic) {
            $html .= $lic->toHtml($i + 1);
        }
        $html .= '</div>';
    }*/

    // LineItemCharge (table)

if (!empty($this->LineItemCharge)) {
    $ctx = 'cg-'.($index ?? 0);
    $html .= renderItemsTable($this->LineItemCharge, [], 'Line Item Charges', true, $ctx);
}


    /* LineItemHeader (groups that may contain sub-charges)
    if (!empty($this->LineItemHeader)) {
        $html .= '<div class="lineitem-headers stack">';
        foreach ($this->LineItemHeader as $i => $lih) {
            $html .= $lih->toHtml($i + 1);
        }
        $html .= '</div>';
    }*/

    // LineItemHeader (groups that may contain sub-charges)
    if (!empty($this->LineItemHeader)) {
        $html .= '<div class="lineitem-headers stack">';
        foreach ($this->LineItemHeader as $i => $lih) {
            $html .= $lih->toHtml($i + 1); // headers render their OWN tables
        }
        $html .= '</div>';
    }

    $html .= '</section>';
    return $html;
}

}

class LineItemHeader extends ChargeGroup
{
    public $Header;
    public $RateSchedule;
    public $LineItemCharge1 = []; // Array of LineItemCharge1
    public $LineItemSubHeader = []; // Array of LineItemSubHeader
   
    public function __construct(array $d = []) 
    {
        $this->Header       = pick($d, 'Header');
        $this->RateSchedule = pick($d, 'RateSchedule');

        foreach (listify(pick($d, 'LineItemCharge1', [])) as $row) {
            $this->LineItemCharge1[] = new LineItemCharge1($row);
        }
        foreach (listify(pick($d, 'LineItemSubHeader', [])) as $row) {
            $this->LineItemSubHeader[] = new LineItemSubHeader($row);
        }
    }

    // --- inside class LineItemHeader ---
public function toHtml(int $index = null): string 
{
    $title = 'Line Item Header' . ($index ? " #$index" : '');
    $html  = '<section class="card lineitem-header">';
    $html .= '<h6>'.h($title).'</h6>';
    $html .= '<dl class="kv">';
    $html .= kv('Header',       $this->Header);
    $html .= kv('Rate Schedule',$this->RateSchedule);
    $html .= '</dl>';

    /*
    // Child charges directly under this header
    if (!empty($this->LineItemCharge1)) 
        {
        $html .= '<div class="lineitem-charge1s stack">';
        foreach ($this->LineItemCharge1 as $i => $c1) {
            $html .= $c1->toHtml($i + 1);
        }
        $html .= '</div>';
    }

    // Sub-headers (with their own charges)
    if (!empty($this->LineItemSubHeader)) 
        {
        $html .= '<div class="lineitem-subheaders stack">';
        foreach ($this->LineItemSubHeader as $i => $sub) {
            $html .= $sub->toHtml($i + 1);
        }
        $html .= '</div>';
    } */

if (!empty($this->LineItemCharge1)) {
    $ctx = 'hdr-'.($index ?? 0);
    $html .= renderItemsTable($this->LineItemCharge1, [], 'Charges under Header', true, $ctx);
}

    if (!empty($this->LineItemSubHeader)) 
        {
        $html .= '<div class="lineitem-subheaders stack">';
        foreach ($this->LineItemSubHeader as $i => $sub) {
            $html .= $sub->toHtml($i + 1); // subheaders render their OWN tables
        }
        $html .= '</div>';
    }

    $html .= '</section>';
    return $html;
}

}

class LineItemCharge1 extends LineItemHeader 
{
    public $Amount;
    public $IsNegative;
    public $Description;
    public $Equation;
    public $Tier;
    public $ServicePeriod;
    public $ServicePeriodText;
    public $ServiceStartAt;
    public $ServiceEndAt;

    public function __construct(array $d = []) 
    {
        $this->Amount           = pick($d, 'Amount');
        $this->IsNegative       = pick($d, 'IsNegative');
        $this->Description      = pick($d, 'Description');
        $this->Equation         = pick($d, 'Equation');
        $this->Tier             = pick($d, 'Tier');
        $this->ServicePeriod    = pick($d, 'ServicePeriod');
        $this->ServicePeriodText= pick($d, 'ServicePeriodText');
        $this->ServiceStartAt   = pick($d, 'ServiceStartAt');
        $this->ServiceEndAt     = pick($d, 'ServiceEndAt');
    }
    // --- inside class LineItemCharge1 ---
    public function toHtml(int $index = null): string {
        $title = 'Line Item Charge 1' . ($index ? " #$index" : '');
        $html  = '<section class="card lineitem-charge1">';
        $html .= '<h6>'.h($title).'</h6>';
        $html .= '<dl class="kv">';
        $html .= kv('Description',        $this->Description);
        $html .= kv('Amount',             $this->Amount);
        //$html .= kv('Is Negative',        $this->IsNegative ? 'true' : 'false');
        $html .= kv('Equation',           $this->Equation);
        //$html .= kv('Tier',               $this->Tier);
        $html .= kv('Service Period',     $this->ServicePeriod);
        //$html .= kv('Service Period Text',$this->ServicePeriodText);
        $html .= kv('Service Start At',   $this->ServiceStartAt);
        $html .= kv('Service End At',     $this->ServiceEndAt);
        $html .= '</dl>';
        $html .= '</section>';
        return $html;
    }

}

class LineItemSubHeader extends LineItemHeader 
{
    public $SubHeader;
    public $RateSchedule;
    public $LineItemCharge2 = []; // Array of LineItemCharge2

    public function __construct(array $d = []) 
    {
        $this->SubHeader   = pick($d, 'SubHeader');
        $this->RateSchedule= pick($d, 'RateSchedule');

        foreach (listify(pick($d, 'LineItemCharge2', [])) as $row) {
            $this->LineItemCharge2[] = new LineItemCharge2($row);
        }
    }

    // --- inside class LineItemSubHeader ---
    public function toHtml(int $index = null): string {
        $title = 'Line Item Sub-Header' . ($index ? " #$index" : '');
        $html  = '<section class="card lineitem-subheader">';
        $html .= '<h6>'.h($title).'</h6>';
        $html .= '<dl class="kv">';
        $html .= kv('SubHeader',    $this->SubHeader);
        $html .= kv('Rate Schedule',$this->RateSchedule);
        $html .= '</dl>';

        /*
        if (!empty($this->LineItemCharge2)) {
            $html .= '<div class="lineitem-charge2s stack">';
            foreach ($this->LineItemCharge2 as $i => $c2) {
                $html .= $c2->toHtml($i + 1);
            }
            $html .= '</div>';
        }*/

if (!empty($this->LineItemCharge2)) {
    $ctx = 'sub-'.($index ?? 0);
    $html .= renderItemsTable($this->LineItemCharge2, [], 'Charges under Subheader', true, $ctx);
}

        $html .= '</section>';
        return $html;
    }
    

}

class LineItemCharge2 extends LineItemSubHeader 
{
    public $Amount;
    public $IsNegative;
    public $Description;
    public $Equation;
    public $Tier;
    public $ServicePeriod;
    public $ServicePeriodText;
    public $ServiceStartAt;
    public $ServiceEndAt;

    public function __construct(array $d = []) 
    {
        $this->Amount            = pick($d, 'Amount');
        $this->IsNegative        = pick($d, 'IsNegative');
        $this->Description       = pick($d, 'Description');
        $this->Equation          = pick($d, 'Equation');
        $this->Tier              = pick($d, 'Tier');
        $this->ServicePeriod     = pick($d, 'ServicePeriod');
        $this->ServicePeriodText = pick($d, 'ServicePeriodText');
        $this->ServiceStartAt    = pick($d, 'ServiceStartAt');
        $this->ServiceEndAt      = pick($d, 'ServiceEndAt');
    }

    // --- inside class LineItemCharge2 ---
    public function toHtml(int $index = null): string {
        $title = 'Line Item Charge 2' . ($index ? " #$index" : '');
        $html  = '<section class="card lineitem-charge2">';
        $html .= '<h6>'.h($title).'</h6>';
        $html .= '<dl class="kv">';
        $html .= kv('Description',        $this->Description);
        $html .= kv('Amount',             $this->Amount);
        //$html .= kv('Is Negative',        $this->IsNegative ? 'true' : 'false');
        $html .= kv('Equation',           $this->Equation);
        $html .= kv('Tier',               $this->Tier);
        $html .= kv('Service Period',     $this->ServicePeriod);
        //$html .= kv('Service Period Text',$this->ServicePeriodText);
        $html .= kv('Service Start At',   $this->ServiceStartAt);
        $html .= kv('Service End At',     $this->ServiceEndAt);
        $html .= '</dl>';
        $html .= '</section>';
        return $html;
    }

}

class LineItemCharge extends ChargeGroup 
{
    public $Amount;
    public $IsNegative;
    public $Description;
    public $Equation;
    public $Tier;
    public $ServicePeriod;
    public $ServicePeriodText;
    public $ServiceStartAt;
    public $ServiceEndAt;

    public function __construct(array $d = []) 
    {
        $this->Amount            = pick($d, 'Amount');
        $this->IsNegative        = pick($d, 'IsNegative');
        $this->Description       = pick($d, 'Description');
        $this->Equation          = pick($d, 'Equation');
        $this->Tier              = pick($d, 'Tier');
        $this->ServicePeriod     = pick($d, 'ServicePeriod');
        $this->ServicePeriodText = pick($d, 'ServicePeriodText');
        $this->ServiceStartAt    = pick($d, 'ServiceStartAt');
        $this->ServiceEndAt      = pick($d, 'ServiceEndAt');
    }

    // --- inside class LineItemCharge ---
    public function toHtml(int $index = null): string {
        $title = 'Line Item Charge' . ($index ? " #$index" : '');
        $html  = '<section class="card lineitem-charge">';
        $html .= '<h6>'.h($title).'</h6>';
        $html .= '<dl class="kv">';
        $html .= kv('Description',       $this->Description);
        $html .= kv('Amount',            $this->Amount);
        //$html .= kv('Is Negative',       $this->IsNegative ? 'true' : 'false');
        $html .= kv('Equation',          $this->Equation);
        //$html .= kv('Tier',              $this->Tier);
        $html .= kv('Service Period',    $this->ServicePeriod);
        //$html .= kv('Service Period Text',$this->ServicePeriodText);
        $html .= kv('Service Start At',  $this->ServiceStartAt);
        $html .= kv('Service End At',    $this->ServiceEndAt);
        $html .= '</dl>';
        $html .= '</section>';
        return $html;
    }

}

class Address extends AccountInvoice
{
    public $ServiceAddress;
    public $LocationID;
    public $ServiceChargeGroup = []; // Array of ServiceChargeGroup
    public function __construct(array $d = []) 
    {
        $this->ServiceAddress = pick($d, 'ServiceAddress');
        $this->LocationID     = pick($d, 'LocationID');

        foreach (listify(pick($d, 'ServiceChargeGroup', [])) as $row) {
            $this->ServiceChargeGroup[] = new ServiceChargeGroup($row);
        }
    }

    // --- inside class Address ---
public function toHtml(int $index = null): string {
    $title = 'Address' . ($index ? " #$index" : '');
    $html  = '<section class="card address">';
    $html .= '<h6>'.h($title).'</h6>';
    $html .= '<dl class="kv">';
    $html .= kv('Service Address', $this->ServiceAddress);
    $html .= kv('Location ID',     $this->LocationID);
    $html .= '</dl>';

    if (!empty($this->ServiceChargeGroup)) {
        $html .= '<div class="service-charge-groups stack">';
        foreach ($this->ServiceChargeGroup as $i => $scg) {
            $html .= $scg->toHtml($i + 1);
        }
        $html .= '</div>';
    }

    $html .= '</section>';
    return $html;
}

}

class ServiceChargeGroup extends Address
{
    public $ServicePeriod;
    public $ServicePeriodText;
    public $ServiceStartAt;    
    public $ServiceEndAt;
    public $DaysOfService;
    public $ServiceDate;
    public $ServiceContractNumber;
    public $RateSchedule;
    public $TotalCharge;
    public $LineItemHeader1 = []; // Array of LineItemHeader1
    public $LineItemCharge3 = []; // Array of LineItemCharge3
    public $MeterGroup      = []; // Array of MeterGroup

    public function __construct(array $d = []) {
        $this->ServicePeriod        = pick($d, 'ServicePeriod');
        $this->ServicePeriodText    = pick($d, 'ServicePeriodText');
        $this->ServiceStartAt       = pick($d, 'ServiceStartAt');
        $this->ServiceEndAt         = pick($d, 'ServiceEndAt');
        $this->DaysOfService        = pick($d, 'DaysOfService');
        $this->ServiceDate          = pick($d, 'ServiceDate');
        $this->ServiceContractNumber= pick($d, 'ServiceContractNumber');
        $this->RateSchedule         = pick($d, 'RateSchedule');
        $this->TotalCharge          = pick($d, 'TotalCharge');

        foreach (listify(pick($d, 'LineItemHeader1', [])) as $row) {
            $this->LineItemHeader1[] = new LineItemHeader1($row);
        }
        foreach (listify(pick($d, 'LineItemCharge3', [])) as $row) {
            $this->LineItemCharge3[] = new LineItemCharge3($row);
        }
        foreach (listify(pick($d, 'MeterGroup', [])) as $row) {
            $this->MeterGroup[] = new MeterGroup($row);
        }
    }

    // --- inside class ServiceChargeGroup ---
public function toHtml(int $index = null): string {
    $title = 'Service Charge Group' . ($index ? " #$index" : '');
    $html  = '<section class="card service-charge-group">';
    $html .= '<h6>'.h($title).'</h6>';
    $html .= '<dl class="kv">';
    $html .= kv('Service Period',        $this->ServicePeriod);
    //$html .= kv('Service Period Text',   $this->ServicePeriodText);
    $html .= kv('Service Start At',      $this->ServiceStartAt);
    $html .= kv('Service End At',        $this->ServiceEndAt);
    $html .= kv('Days Of Service',       $this->DaysOfService);
    $html .= kv('Service Date',          $this->ServiceDate);
    $html .= kv('Service Contract #',    $this->ServiceContractNumber);
    $html .= kv('Rate Schedule',         $this->RateSchedule);
    $html .= kv('Total Charge',          $this->TotalCharge);
    $html .= '</dl>';

if (!empty($this->LineItemCharge3)) {
    $ctx = 'scg-'.($index ?? 0);
    $html .= renderItemsTable($this->LineItemCharge3, [], 'Line Item Charges', true, $ctx);
}

    if (!empty($this->LineItemHeader1)) {
        $html .= '<div class="lineitem-header1s stack">';
        foreach ($this->LineItemHeader1 as $i => $h1) {
            $html .= $h1->toHtml($i + 1);
        }
        $html .= '</div>';
    }

    if (!empty($this->MeterGroup)) {
        $html .= '<div class="meter-groups stack">';
        foreach ($this->MeterGroup as $i => $mg) {
            $html .= $mg->toHtml($i + 1);
        }
        $html .= '</div>';
    }

    $html .= '</section>';
    return $html;
}

}

class LineItemCharge3 extends ServiceChargeGroup
{
    public $Amount;
    public $IsNegative;
    public $Description;
    public $Equation;
    public $Tier;
    public $ServicePeriod;
    public $ServicePeriodText;
    public $ServiceStartAt;
    public $ServiceEndAt;

    public function __construct(array $d = []) 
    {
        $this->Amount            = pick($d, 'Amount');
        $this->IsNegative        = pick($d, 'IsNegative');
        $this->Description       = pick($d, 'Description');
        $this->Equation          = pick($d, 'Equation');
        $this->Tier              = pick($d, 'Tier');
        $this->ServicePeriod     = pick($d, 'ServicePeriod');
        $this->ServicePeriodText = pick($d, 'ServicePeriodText');
        $this->ServiceStartAt    = pick($d, 'ServiceStartAt');
        $this->ServiceEndAt      = pick($d, 'ServiceEndAt');
    }

    // --- inside class LineItemCharge3 ---
public function toHtml(int $index = null): string {
    $title = 'Line Item Charge 3' . ($index ? " #$index" : '');
    $html  = '<section class="card lineitem-charge3">';
    $html .= '<h6>'.h($title).'</h6>';
    $html .= '<dl class="kv">';
    $html .= kv('Description',        $this->Description);
    $html .= kv('Amount',             $this->Amount);
    //$html .= kv('Is Negative',        $this->IsNegative ? 'true' : 'false');
    $html .= kv('Equation',           $this->Equation);
    //$html .= kv('Tier',               $this->Tier);
    $html .= kv('Service Period',     $this->ServicePeriod);
    //$html .= kv('Service Period Text',$this->ServicePeriodText);
    $html .= kv('Service Start At',   $this->ServiceStartAt);
    $html .= kv('Service End At',     $this->ServiceEndAt);
    $html .= '</dl>';
    $html .= '</section>';
    return $html;
}

}

class LineItemHeader1 extends ServiceChargeGroup
{
    public $Header;
    public $RateSchedule;
    public $LineItemCharge4 = [];   // Array of LineItemCharge4
    public $LineItemSubHeader1 = []; // Array of LineItemSubHeader1

    public function __construct(array $d = []) 
    {
        $this->Header       = pick($d, 'Header');
        $this->RateSchedule = pick($d, 'RateSchedule');

        foreach (listify(pick($d, 'LineItemCharge4', [])) as $row) {
            $this->LineItemCharge4[] = new LineItemCharge4($row);
        }
        foreach (listify(pick($d, 'LineItemSubHeader1', [])) as $row) {
            $this->LineItemSubHeader1[] = new LineItemSubHeader1($row);
        }
    }

    // --- inside class LineItemHeader1 ---
public function toHtml($index = null): string {
    $title = 'Line Item Header 1' . ($index ? " #$index" : '');
    $html  = '<section class="card lineitem-header1">';
    $html .= '<h6>'.h($title).'</h6>';
    $html .= '<dl class="kv">';
    $html .= kv('Header',        $this->Header);
    $html .= kv('Rate Schedule', $this->RateSchedule);
    $html .= '</dl>';

if (!empty($this->LineItemCharge4)) {
    $ctx = 'hdr1-'.($index ?? 0);
    $html .= renderItemsTable($this->LineItemCharge4, [], 'Charges under Header 1', true, $ctx);
}
    if (!empty($this->LineItemSubHeader1)) {
        $html .= '<div class="lineitem-subheader1s stack">';
        foreach ($this->LineItemSubHeader1 as $i => $s1) {
            $html .= $s1->toHtml($i + 1); // subheader1 renders its OWN tables
        }
        $html .= '</div>';
    }

    $html .= '</section>';
    return $html;
}

}

class LineItemCharge4 extends LineItemHeader1 
{
    public $Amount;
    public $IsNegative; 
    public $Description;
    public $Equation;
    public $Tier;
    public $ServicePeriod;
    public $ServicePeriodText;
    public $ServiceStartAt;
    public $ServiceEndAt;

    public function __construct(array $d = []) 
    {
        $this->Amount            = pick($d, 'Amount');
        $this->IsNegative        = pick($d, 'IsNegative');
        $this->Description       = pick($d, 'Description');
        $this->Equation          = pick($d, 'Equation');
        $this->Tier              = pick($d, 'Tier');
        $this->ServicePeriod     = pick($d, 'ServicePeriod');
        $this->ServicePeriodText = pick($d, 'ServicePeriodText');
        $this->ServiceStartAt    = pick($d, 'ServiceStartAt');
        $this->ServiceEndAt      = pick($d, 'ServiceEndAt');
    }

    // --- inside class LineItemCharge4 ---
    public function toHtml($index = null): string {
        $title = 'Line Item Charge 4' . ($index ? " #$index" : '');
        $html  = '<section class="card lineitem-charge4">';
        $html .= '<h6>'.h($title).'</h6>';
        $html .= '<dl class="kv">';
        $html .= kv('Description',        $this->Description);
        $html .= kv('Amount',             $this->Amount);
        //$html .= kv('Is Negative',        $this->IsNegative ? 'true' : 'false');
        $html .= kv('Equation',           $this->Equation);
        //$html .= kv('Tier',               $this->Tier);
        $html .= kv('Service Period',     $this->ServicePeriod);
        //$html .= kv('Service Period Text',$this->ServicePeriodText);
        $html .= kv('Service Start At',   $this->ServiceStartAt);
        $html .= kv('Service End At',     $this->ServiceEndAt);
        $html .= '</dl>';
        $html .= '</section>';
        return $html;
    }

}

class LineItemSubHeader1 extends LineItemHeader1
{
    public $SubHeader;
    public $RateSchedule;
    public $LineItemCharge5 = []; // Array of LineItemCharge5

    public function __construct(array $d = []) 
    {
        $this->SubHeader   = pick($d, 'SubHeader');
        $this->RateSchedule= pick($d, 'RateSchedule');

        foreach (listify(pick($d, 'LineItemCharge5', [])) as $row) {
            $this->LineItemCharge5[] = new LineItemCharge5($row);
        }
    }

    // --- inside class LineItemSubHeader1 ---
public function toHtml($index = null): string {
    $title = 'Line Item Sub-Header 1' . ($index ? " #$index" : '');
    $html  = '<section class="card lineitem-subheader1">';
    $html .= '<h6>'.h($title).'</h6>';
    $html .= '<dl class="kv">';
    $html .= kv('SubHeader',     $this->SubHeader);
    $html .= kv('Rate Schedule', $this->RateSchedule);
    $html .= '</dl>';

if (!empty($this->LineItemCharge5)) {
    $ctx = 'sub1-'.($index ?? 0);
    $html .= renderItemsTable($this->LineItemCharge5, [], 'Charges under Subheader 1', true, $ctx);
}

    $html .= '</section>';
    return $html;
}

}


class LineItemCharge5 extends LineItemSubHeader1 
{
    public $Amount;
    public $IsNegative;
    public $Description;
    public $Equation;
    public $Tier;
    public $ServicePeriod;
    public $ServicePeriodText;
    public $ServiceStartAt;
    public $ServiceEndAt;

    public function __construct(array $d = []) 
    {
        $this->Amount            = pick($d, 'Amount');
        $this->IsNegative        = pick($d, 'IsNegative');
        $this->Description       = pick($d, 'Description');
        $this->Equation          = pick($d, 'Equation');
        $this->Tier              = pick($d, 'Tier');
        $this->ServicePeriod     = pick($d, 'ServicePeriod');
        $this->ServicePeriodText = pick($d, 'ServicePeriodText');
        $this->ServiceStartAt    = pick($d, 'ServiceStartAt');
        $this->ServiceEndAt      = pick($d, 'ServiceEndAt');
    }

    // --- inside class LineItemCharge5 ---
public function toHtml($index = null): string {
    $title = 'Line Item Charge 5' . ($index ? " #$index" : '');
    $html  = '<section class="card lineitem-charge5">';
    $html .= '<h6>'.h($title).'</h6>';
    $html .= '<dl class="kv">';
    $html .= kv('Description',        $this->Description);
    $html .= kv('Amount',             $this->Amount);
    //$html .= kv('Is Negative',        $this->IsNegative ? 'true' : 'false');
    $html .= kv('Equation',           $this->Equation);
    //$html .= kv('Tier',               $this->Tier);
    $html .= kv('Service Period',     $this->ServicePeriod);
    //$html .= kv('Service Period Text',$this->ServicePeriodText);
    $html .= kv('Service Start At',   $this->ServiceStartAt);
    $html .= kv('Service End At',     $this->ServiceEndAt);
    $html .= '</dl>';
    $html .= '</section>';
    return $html;

    
}

}

class MeterGroup extends ServiceChargeGroup
{
    public $ServicePeriod;
    public $ServicePeriodText;
    public $ServiceStartAt;
    public $ServiceEndAt;
    public $DaysOfService;
    public $TotalMeteredUsage;
    public $TotalMeteredUsageUnitOfMeasure;
    public $TotalConvertedUsage;
    public $TotalConvertedUsageUnitOfMeasure;
    public $Meter = []; // Array of Meter

    public function __construct(array $d = []) 
    {
        $this->ServicePeriod                      = pick($d, 'ServicePeriod');
        $this->ServicePeriodText                  = pick($d, 'ServicePeriodText');
        $this->ServiceStartAt                     = pick($d, 'ServiceStartAt');
        $this->ServiceEndAt                       = pick($d, 'ServiceEndAt');
        $this->DaysOfService                      = pick($d, 'DaysOfService');
        $this->TotalMeteredUsage                  = pick($d, 'TotalMeteredUsage');
        $this->TotalMeteredUsageUnitOfMeasure     = pick($d, 'TotalMeteredUsageUnitOfMeasure');
        $this->TotalConvertedUsage                = pick($d, 'TotalConvertedUsage');
        $this->TotalConvertedUsageUnitOfMeasure   = pick($d, 'TotalConvertedUsageUnitOfMeasure');

        foreach (listify(pick($d, 'Meter', [])) as $row) {
            $this->Meter[] = new Meter($row);
        }
    }
    // --- inside class MeterGroup ---
public function toHtml($index = null): string {
    $title = 'Meter Group' . ($index ? " #$index" : '');
    $html  = '<section class="card meter-group">';
    $html .= '<h6>'.h($title).'</h6>';
    $html .= '<dl class="kv">';
    $html .= kv('Service Period',                        $this->ServicePeriod);
    $html .= kv('Service Period Text',                   $this->ServicePeriodText);
    $html .= kv('Service Start At',                      $this->ServiceStartAt);
    $html .= kv('Service End At',                        $this->ServiceEndAt);
    $html .= kv('Days Of Service',                       $this->DaysOfService);
    $html .= kv('Total Metered Usage',                   $this->TotalMeteredUsage);
    $html .= kv('Total Metered UoM',                     $this->TotalMeteredUsageUnitOfMeasure);
    $html .= kv('Total Converted Usage',                 $this->TotalConvertedUsage);
    $html .= kv('Total Converted UoM',                   $this->TotalConvertedUsageUnitOfMeasure);
    $html .= '</dl>';

    if (!empty($this->Meter)) {
        $html .= '<div class="meters stack">';
        foreach ($this->Meter as $i => $m) {
            $html .= $m->toHtml($i + 1);
        }
        $html .= '</div>';
    }

    $html .= '</section>';
    return $html;
}

}

class Meter extends MeterGroup
{
    public $MeterNumber;
    public $MeasureName;
    public $MeasureNameHeader;
    public $MeasurePurposeHeader;
    public $MeasureSeasonHeader;
    public $MeasureElectricityFlowHeader;
    public $TotalMeteredUsage;
    public $TotalMeteredUsageUnitOfMeasure;
    public $TotalConvertedUsage;
    public $TotalConvertedUsageUnitOfMeasure;
    public $MeterRead = []; // Array of MeterRead

    public function __construct(array $d = []) 
    {
        $this->MeterNumber                       = pick($d, 'MeterNumber');
        $this->MeasureName                       = pick($d, 'MeasureName');
        $this->MeasureNameHeader                 = pick($d, 'MeasureNameHeader');
        $this->MeasurePurposeHeader              = pick($d, 'MeasurePurposeHeader');
        $this->MeasureSeasonHeader               = pick($d, 'MeasureSeasonHeader');
        $this->MeasureElectricityFlowHeader      = pick($d, 'MeasureElectricityFlowHeader');
        $this->TotalMeteredUsage                 = pick($d, 'TotalMeteredUsage');
        $this->TotalMeteredUsageUnitOfMeasure    = pick($d, 'TotalMeteredUsageUnitOfMeasure');
        $this->TotalConvertedUsage               = pick($d, 'TotalConvertedUsage');
        $this->TotalConvertedUsageUnitOfMeasure  = pick($d, 'TotalConvertedUsageUnitOfMeasure');

        foreach (listify(pick($d, 'MeterRead', [])) as $row) {
            $this->MeterRead[] = new MeterRead($row);
        }
    }

    // --- inside class Meter ---
public function toHtml($index = null): string {
    $title = 'Meter' . ($index ? " #$index" : '');
    $html  = '<section class="card meter">';
    $html .= '<h6>'.h($title).'</h6>';
    $html .= '<dl class="kv">';
    $html .= kv('Meter Number',                     $this->MeterNumber);
    $html .= kv('Measure Name',                     $this->MeasureName);
    $html .= kv('Measure Name Header',              $this->MeasureNameHeader);
    $html .= kv('Measure Purpose Header',           $this->MeasurePurposeHeader);
    $html .= kv('Measure Season Header',            $this->MeasureSeasonHeader);
    $html .= kv('Measure Electricity Flow Header',  $this->MeasureElectricityFlowHeader);
    $html .= kv('Total Metered Usage',              $this->TotalMeteredUsage);
    $html .= kv('Total Metered UoM',                $this->TotalMeteredUsageUnitOfMeasure);
    $html .= kv('Total Converted Usage',            $this->TotalConvertedUsage);
    $html .= kv('Total Converted UoM',              $this->TotalConvertedUsageUnitOfMeasure);
    $html .= '</dl>';

    if (!empty($this->MeterRead)) {
        $html .= '<div class="meter-reads stack">';
        foreach ($this->MeterRead as $i => $mr) {
            $html .= $mr->toHtml($i + 1);
        }
        $html .= '</div>';
    }

    $html .= '</section>';
    return $html;
}

}

class MeterRead extends Meter
{
    public $MeasureName;
    public $MeasurePurpose;
    public $MeasureSignificance;
    public $MeasureSeason;
    public $MeasureElectricityFlow;
    public $ServicePeriod;
    public $ServicePeriodText;
    public $CurrentReadAt;
    public $CurrentRead;
    public $CurrentReadType;
    public $PreviousReadAt;
    public $PreviousRead;
    public $PreviousReadType;
    public $ReadDifference;
    public $ReadUnitOfMeasure;
    public $DaysOfService;
    public $NextReadOn;
    public $MeterRollover;
    public $MeterMultiplier;
    public $AdjustmentFactor;
    public $MeteredUsage;
    public $MeteredUsageUnitOfMeasure;
    public $ConversionFactor;
    public $ConvertedUsage;
    public $ConvertedUsageUnitOfMeasure;
    public $BilledUsage;
    public $BilledUsageUnitOfMeasure;

    public function __construct(array $d = []) 
    {
        $this->MeasureName                 = pick($d, 'MeasureName');
        $this->MeasurePurpose              = pick($d, 'MeasurePurpose');
        $this->MeasureSignificance         = pick($d, 'MeasureSignificance');
        $this->MeasureSeason               = pick($d, 'MeasureSeason');
        $this->MeasureElectricityFlow      = pick($d, 'MeasureElectricityFlow');
        $this->ServicePeriod               = pick($d, 'ServicePeriod');
        $this->ServicePeriodText           = pick($d, 'ServicePeriodText');
        $this->CurrentReadAt               = pick($d, 'CurrentReadAt');
        $this->CurrentRead                 = pick($d, 'CurrentRead');
        $this->CurrentReadType             = pick($d, 'CurrentReadType');
        $this->PreviousReadAt              = pick($d, 'PreviousReadAt');
        $this->PreviousRead                = pick($d, 'PreviousRead');
        $this->PreviousReadType            = pick($d, 'PreviousReadType');
        $this->ReadDifference              = pick($d, 'ReadDifference');
        $this->ReadUnitOfMeasure           = pick($d, 'ReadUnitOfMeasure');
        $this->DaysOfService               = pick($d, 'DaysOfService');
        $this->NextReadOn                  = pick($d, 'NextReadOn');
        $this->MeterRollover               = pick($d, 'MeterRollover');
        $this->MeterMultiplier             = pick($d, 'MeterMultiplier');
        $this->AdjustmentFactor            = pick($d, 'AdjustmentFactor');
        $this->MeteredUsage                = pick($d, 'MeteredUsage');
        $this->MeteredUsageUnitOfMeasure   = pick($d, 'MeteredUsageUnitOfMeasure');
        $this->ConversionFactor            = pick($d, 'ConversionFactor');
        $this->ConvertedUsage              = pick($d, 'ConvertedUsage');
        $this->ConvertedUsageUnitOfMeasure = pick($d, 'ConvertedUsageUnitOfMeasure');
        $this->BilledUsage                 = pick($d, 'BilledUsage');
        $this->BilledUsageUnitOfMeasure    = pick($d, 'BilledUsageUnitOfMeasure');
    }

    // --- inside class MeterRead ---
public function toHtml($index = null): string {
    $title = 'Meter Read' . ($index ? " #$index" : '');
    $html  = '<section class="card meter-read">';
    $html .= '<h6>'.h($title).'</h6>';
    $html .= '<dl class="kv">';
    $html .= kv('Measure Name',               $this->MeasureName);
    $html .= kv('Measure Purpose',            $this->MeasurePurpose);
    $html .= kv('Measure Significance',       $this->MeasureSignificance);
    $html .= kv('Measure Season',             $this->MeasureSeason);
    $html .= kv('Measure Electricity Flow',   $this->MeasureElectricityFlow);
    $html .= kv('Service Period',             $this->ServicePeriod);
    $html .= kv('Service Period Text',        $this->ServicePeriodText);
    $html .= kv('Current Read At',            $this->CurrentReadAt);
    $html .= kv('Current Read',               $this->CurrentRead);
    $html .= kv('Current Read Type',          $this->CurrentReadType);
    $html .= kv('Previous Read At',           $this->PreviousReadAt);
    $html .= kv('Previous Read',              $this->PreviousRead);
    $html .= kv('Previous Read Type',         $this->PreviousReadType);
    $html .= kv('Read Difference',            $this->ReadDifference);
    $html .= kv('Read UoM',                   $this->ReadUnitOfMeasure);
    $html .= kv('Days Of Service',            $this->DaysOfService);
    $html .= kv('Next Read On',               $this->NextReadOn);
    $html .= kv('Meter Rollover',             $this->MeterRollover ? 'true' : 'false');
    $html .= kv('Meter Multiplier',           $this->MeterMultiplier);
    $html .= kv('Adjustment Factor',          $this->AdjustmentFactor);
    $html .= kv('Metered Usage',              $this->MeteredUsage);
    $html .= kv('Metered UoM',                $this->MeteredUsageUnitOfMeasure);
    $html .= kv('Conversion Factor',          $this->ConversionFactor);
    $html .= kv('Converted Usage',            $this->ConvertedUsage);
    $html .= kv('Converted UoM',              $this->ConvertedUsageUnitOfMeasure);
    $html .= kv('Billed Usage',               $this->BilledUsage);
    $html .= kv('Billed UoM',                 $this->BilledUsageUnitOfMeasure);
    $html .= '</dl>';
    $html .= '</section>';
    return $html;
}

}
