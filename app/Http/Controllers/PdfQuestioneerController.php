<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExcellRequest;
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
    public function uploadExcell(ExcellRequest $request)
    {
        return $this->pdfRepository->uploadExcell($request);
    }
    public function getExports()
    {
        return $this->pdfRepository->getExports();
    }
    public function getExcellDetails(Request $request)
    {
        return $this->pdfRepository->getExcellDetails($request);
    }
    
    public function uploadExcellExports(ExcellRequest $request)
    {
        return $this->pdfRepository->uploadExcellExports($request);
    }
   
}
