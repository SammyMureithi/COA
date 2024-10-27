<?php
namespace App\Repositories\Pdf;

use App\Http\Requests\ExcellRequest;
use App\Http\Requests\PdfQuestioneer;

interface PdfRepositoryInterface
{
  
    public function readAndFillQuestioneers(PdfQuestioneer $request);
    public function getTestDetails();
    public function uploadExcell(ExcellRequest $request);
    public function getExports();
}
