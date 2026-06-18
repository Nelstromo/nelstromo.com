<?php

/**
 * The LineItem Class represents the data structure for a line item as it is represented in Line Item Manager.
 * 
 * Said data will be imported by the user from a csv file, and will be used to check against the data imported from the utility bill.
 */
class LineItem
{
    public $ID; 
    public $Header; // Important
    public $Name; // Important
    public $Equation; // Important
    public $ChargeType;
    public $UtilityNetwork;
    public $ServiceType;
    public $EnergySource;
    public $Level;
    public $Flags;
    public $ControlNumber;
    public $CorrelationCode;
    public $LinkedMeasure;
    public $State;
    public $IsInformational; // Important
    public $IsConsumption;
    public $IsMeasured;
    public $IsSummary;// Important
    public $Active; // Important 
    public $AmountSign; // Important
    public $HasRules; // Important
    public $IsNonElectiveServiceFee;
    public $History;

    public function __construct($data)
    {
        $this->ID = $data[0] ?? null;
        $this->Header = $data[1] ?? null;
        $this->Name = $data[2] ?? null;
        $this->Equation = $data[3] ?? null;
        $this->ChargeType = $data[4] ?? null;
        $this->UtilityNetwork = $data[5] ?? null;
        $this->ServiceType = $data[6] ?? null;
        $this->EnergySource = $data[7] ?? null;
        $this->Level = $data[8] ?? null;
        $this->Flags = $data[9] ?? null;
        $this->ControlNumber = $data[10] ?? null;
        $this->CorrelationCode = $data[11] ?? null;
        $this->LinkedMeasure = $data[12] ?? null;
        $this->State = $data[13] ?? null;
        $this->IsInformational = $data[14] ?? null;
        $this->IsConsumption = $data[15] ?? null;
        $this->IsMeasured = $data[16] ?? null;
        $this->IsSummary = $data[17] ?? null;
        $this->Active = $data[18] ?? null;
        $this->AmountSign = $data[19] ?? null;
        $this->HasRules = $data[20] ?? null;
        $this->IsNonElectiveServiceFee = $data[21] ?? null;
        $this->History = $data[22] ?? null;
    }

}
