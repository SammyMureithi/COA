<?php

namespace App\Http\Controllers;

use App\Http\Requests\PdfQuestioneer;
use App\Repositories\Pdf\PdfRepositoryInterface;
use Illuminate\Http\Request;

class PdfQuestioneerController extends Controller
{
    private PdfRepositoryInterface $pdfRepository;
    public function __construct(PdfRepositoryInterface $pdfRepository)
    {
        return $this->pdfRepository= $pdfRepository;
    }
    public function readAndFillQuestioneers(PdfQuestioneer $request)
    {
        return $this->pdfRepository->readAndFillQuestioneers($request);
    }
    public function getTestDetails()
    {
        return $this->pdfRepository->getTestDetails();
    }
   
}
