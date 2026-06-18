<?php
/**
 * The Measure Class represents the data structure for a measure as it is represented in Line Item Manager.
 * 
 * Said data will be imported by the user from a csv file, and will be used to check against the data imported from the utility bill.
 * When reading the data, we will need to explode the data into its component parts, seperated by commas, with each measure being on a new line.
 */
class Measure
{
    public $Name;
    public $Service_Type;
    public $Purpose;
    public $Metered_UoM;
    public $Metered_UoM_Reported;
    public $Billed_UoM;
    public $Billed_UoM_Reported;
    public $Energy_Source;
    public $Flow;
    public $Season;
    public $Time_Flow_Significance;
    public $Has_Device;
    public $Control_Number;
    public $Is_Summary;

    public function __construct($data)
    {
        $this->Name = $data[0] ?? null;
        $this->Service_Type = $data[1] ?? null;
        $this->Purpose = $data[2] ?? null;
        $this->Metered_UoM = $data[3] ?? null;
        $this->Metered_UoM_Reported = $data[4] ?? null;
        $this->Billed_UoM = $data[5] ?? null;
        $this->Billed_UoM_Reported = $data[6] ?? null;
        $this->Energy_Source = $data[7] ?? null;
        $this->Flow = $data[8] ?? null;
        $this->Season = $data[9] ?? null;
        $this->Time_Flow_Significance = $data[10] ?? null;
        $this->Has_Device = $data[11] ?? null;
        $this->Control_Number = $data[12] ?? null;
        $this->Is_Summary = $data[13] ?? null;
    }
}

