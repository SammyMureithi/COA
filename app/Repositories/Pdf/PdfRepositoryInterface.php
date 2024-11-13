<?php
namespace App\Repositories\Pdf;

use App\Http\Requests\ExcellRequest;
use App\Http\Requests\PdfQuestioneer;
use Illuminate\Http\Request;

interface PdfRepositoryInterface
{
  
    public function readAndFillQuestioneers(PdfQuestioneer $request);
    public function getTestDetails();
    public function uploadExcell(ExcellRequest $request);
    public function getExports();
    public function uploadExcellExports(ExcellRequest $request);
    public function getExcellDetails(Request $request);
}
