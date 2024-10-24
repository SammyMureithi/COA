<?php
namespace App\Repositories\Pdf;

use App\Http\Requests\PdfQuestioneer;
use Illuminate\Http\Request;

interface PdfRepositoryInterface
{
  
    public function readAndFillQuestioneers(PdfQuestioneer $request);
    public function getTestDetails();
}
