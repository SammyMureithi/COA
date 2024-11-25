<?php

namespace App\Repositories\Pdf;

use App\Http\Requests\ExcellRequest;
use App\Http\Requests\PdfQuestioneer;
use App\Models\Exports;
use App\Models\TestResults;
use App\Repositories\Pdf\PdfRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Spatie\PdfToText\Pdf;
use Throwable;
use Maatwebsite\Excel\Facades\Excel;


use Carbon\Carbon;
use Illuminate\Http\Request;

class PdfRepository implements PdfRepositoryInterface
{
    public function readAndFillQuestioneers(PdfQuestioneer $request)
    {
        try {
            if ($request->hasFile('file')) {
                $file = $request->file('file');
    
                // Sanitize the file name
                $originalFileName = $file->getClientOriginalName();
                $sanitizedFileName = preg_replace('/[^A-Za-z0-9\-_\.]/', '_', str_replace(' ', '_', $originalFileName));
    
                // Store the uploaded file using Storage facade
                $path = Storage::disk('local')->putFileAs('private/pdfs', $file, $sanitizedFileName);
                $fullPath = storage_path('app/' . $path);
    
                // Extract text from the PDF
                $pdfText = Pdf::getText($fullPath);
    
                // Parse the relevant fields from the PDF content
                $data = $this->extractPdfData($pdfText);
    
                // Clear any previous output buffer to avoid unwanted content in the response
                ob_clean();
    
                return response()->json([
                    'ok' => true,
                    'status' => 'success',
                    'message' => 'Record processed successfully',
                ]);
            } else {
                Log::error('No file found in the request.');
                return response()->json([
                    'ok' => false,
                    'status' => 'error',
                    'message' => 'No PDF file found in the request.',
                ], 400);
            }
        } catch (\Throwable $th) {
            Log::error('Error processing PDF: ' . $th->getMessage());
    
            // Clear any previous output buffer
            ob_clean();
    
            return response()->json([
                'ok' => false,
                'status' => 'error',
                'message' => 'Error occurred while processing the PDF.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
    
private function extractPdfData($pdfText){
    // Remove excessive newlines to handle multi-line values better
    $pdfText = preg_replace("/\n+/", " ", $pdfText);
    // Initialize data array
    $data = [
        'sample_no' => 'N/A',
        'batch' => 'N/A',
        'aceton_insoluble' => 'N/A',
        'acid_value' => 'N/A',
        'color_gardner'=> 'N/A',
        'peroxide_value' => 'N/A',
        'result_based_on_sample_mass' => 'N/A',
        'toluene_insoluble_matter'=> 'N/A',
        'viscosity_25C' => 'N/A' 
    ];
   

    
    // Step 1: Extract the Sample No
    preg_match('/M\s?\d+/', $pdfText, $sampleMatches);
    if (isset($sampleMatches[0])) {
        $data['sample_no'] = $sampleMatches[0];
        // Step 2: Now find the Batch No after the Sample No
        $sampleNoPos = strpos($pdfText, $sampleMatches[0]);
        if ($sampleNoPos !== false) {
            $textAfterSampleNo = substr($pdfText, $sampleNoPos);
            
            // Step 3: Extract Batch No
            preg_match('/BA\d+/', $textAfterSampleNo, $batchMatches);
            if (isset($batchMatches[0])) {
                $data['batch'] = $batchMatches[0];
            } else {
                $data['batch'] = 'N/A'; 
            }
        } else {
            return response()->json([
                'ok' => false,
                'status' => 'error',
                'message' => 'Sample No position not found in the text..',
            ], 500);
        }
    } else {
        $data['sample_no'] = 'N/A'; 
        return response()->json([
            'ok' => false,
            'status' => 'error',
            'message' => 'Sample No not found or pattern mismatch.',
        ], 500);
    }

      // Extract "Aceton insoluble" value
      preg_match('/Aceton insoluble\s*([\d,]+)\s*%/i', $pdfText, $matches);
      $data['aceton_insoluble'] = isset($matches[1]) ? $matches[1] . ' %' : 'N/A';
  
    // Extract "Acid value" value
    preg_match('/Acid value\s*([\d,]+)\s*mg KOH\/g/i', $pdfText, $matches);
    $data['acid_value'] = isset($matches[1]) ? $matches[1] . ' mg KOH/g' : 'N/A';


     // Extract "Color Gardner, dilution 10 (w/w) with toluene" value
     preg_match('/Color Gardner, dilution 10 \(w\/w\) with toluene\s*([\d,]+)/i', $pdfText, $matches);
     $data['color_gardner'] = isset($matches[1]) ? $matches[1] : 'N/A';

      // Extract "Peroxide value" value
    if (preg_match('/Peroxide value\s*(Less than\s*)?([\d,]+)\s*meq O2\/kg/i', $pdfText, $matches)) {
        $data['peroxide_value'] = isset($matches[1]) && trim($matches[1]) === 'Less than'
            ? 'Less than ' . $matches[2] . ' meq O2/kg'
            : $matches[2] . ' meq O2/kg';
    } else {
        $data['peroxide_value'] = 'N/A';
    }
    if (preg_match('/Result based on sample mass of\s*([\d,]+)\s*(\w+)/i', $pdfText, $matches)) {
        $data['result_based_on_sample_mass'] = $matches[1] . ' ' . $matches[2];
    }
     preg_match('/Toluene insoluble matter\s*([\d,]+)\s*%/i', $pdfText, $matches);
    $data['toluene_insoluble_matter'] = isset($matches[1]) ? $matches[1] . ' %' : 'N/A';


    // Extract "Viscosity at 25°C" value
    if (preg_match('/Viscosity at 25°C\s*([\d,]+)\s*(\w+)?/i', $pdfText, $matches)) {
        $data['viscosity_25C'] = isset($matches[2]) ? $matches[1] . ' ' . $matches[2] : $matches[1];
    }
    if (!isset($data['batch']) || $data['batch'] === "N/A") {
        // Adjusted regex to capture batch number
        preg_match('/Batch\s*number.*?\b(B[A-Z0-9]+)/i', $pdfText, $matches);
        $data['batch'] = isset($matches[1]) ? $matches[1] : 'N/A';
        
       
    }
preg_match('/Moisture\s*([\d.,]+)\s*%\s*\(w\/w\)/i', $pdfText, $matches);

$data['moisture'] = isset($matches[1]) ? $matches[1] . ' % (w/w)' : 'N/A';

// Regex to match "Total plate count 30°C" followed by a numeric value and "cfu/g" unit
preg_match('/Total plate count 30°C\s*([\d,]+)\s*cfu\/g/i', $pdfText, $matches);

$data['total_plate_count'] = isset($matches[1]) ? $matches[1] . ' cfu/g' : 'N/A';

// Regex to capture "Arsenic (As) (7440-38-2)" and the value following it
// General regex to capture "Arsenic (As) (7440-38-2)" with variable values
preg_match('/Arsenic \(As\) \(7440-38-2\)\s*(Less than\s*)?([\d.,]+)\s*mg\/kg/i', $pdfText, $matches);

$data['arsenic'] = isset($matches[2]) ? (isset($matches[1]) ? $matches[1] . $matches[2] : $matches[2]) . ' mg/kg' : 'N/A';

preg_match('/(Arsenic \(As\) \(7440-38-2\)|Cadmium \(Cd\) \(7440-43-9\))\s*(Less than\s*)?([\d.,]+)\s*mg\/kg/i', $pdfText, $matches);

// Regex to capture "Cadmium (Cd) (7440-43-9)" with any associated value
preg_match('/Cadmium \(Cd\) \(7440-43-9\)\s*(Less than\s*)?([\d.,]+)\s*mg\/kg/i', $pdfText, $matches);

$data['cadmium'] = isset($matches[2]) ? (isset($matches[1]) ? $matches[1] . $matches[2] : $matches[2]) . ' mg/kg' : 'N/A';
// Regex to capture "Lead (Pb) (7439-92-1)" with any associated value
preg_match('/Lead \(Pb\) \(7439-92-1\)\s*(Less than\s*)?([\d.,]+)\s*mg\/kg/i', $pdfText, $matches);

$data['lead'] = isset($matches[2]) ? (isset($matches[1]) ? $matches[1] . $matches[2] : $matches[2]) . ' mg/kg' : 'N/A';
preg_match('/Mercury \(Hg\) \(7439-97-6\)\s*(Less than\s*)?([\d.,]+)\s*mg\/kg/i', $pdfText, $matches);

$data['mercury'] = isset($matches[2]) ? (isset($matches[1]) ? $matches[1] . $matches[2] : $matches[2]) . ' mg/kg' : 'N/A';
// Regex to capture "Total plate count 30°C" followed by a numeric value and "cfu/g"
preg_match('/Total plate count 30°C\s*([\d.,]+)\s*cfu\/g/i', $pdfText, $matches);

$data['total_plate_count'] = isset($matches[1]) ? $matches[1] . ' cfu/g' : 'N/A';

// Regex to capture "Iron (Fe) (7439-89-6)" with its associated value
preg_match('/Iron \(Fe\) \(7439-89-6\)\s*(Less than\s*)?([\d.,]+)\s*mg\/kg/i', $pdfText, $matches);

$data['iron'] = isset($matches[2]) ? (isset($matches[1]) ? $matches[1] . $matches[2] : $matches[2]) . ' mg/kg' : 'N/A';

// Regex to capture "GMO Screening" test result (either 'positive' or 'negative')
preg_match('/GMO Screening.*?\b(positive|negative)\b/i', $pdfText, $matches);

$data['GMO Screening'] = isset($matches[1]) ? $matches[1] : 'N/A';

preg_match('/Enterobacteriaceae\s*(Less than\s*)?([\d.,]+)\s*cfu\/g/i', $pdfText, $matches);

$data['enterobacteriaceae'] = isset($matches[2]) ? (isset($matches[1]) ? $matches[1] . $matches[2] : $matches[2]) . ' cfu/g' : 'N/A';
 
preg_match('/Total plate count\s*30°C\s*([\d.,]+)\s*cfu\/g/i', $pdfText, $matches);

$data['total_plate_count'] = isset($matches[1]) ? $matches[1] . ' cfu/g' : 'N/A';

preg_match('/Yeasts\s*&\s*moulds\s*(Less than\s*)?([\d.,]+)\s*cfu\/g/i', $pdfText, $matches);

$data['yeasts_and_moulds'] = isset($matches[2]) ? (isset($matches[1]) ? $matches[1] . $matches[2] : $matches[2]) . ' cfu/g' : 'N/A';
preg_match('/Yeasts\s*(Less than\s*)?([\d.,]+)\s*cfu\/g/i', $pdfText, $matches);

$data['yeasts'] = isset($matches[2]) ? (isset($matches[1]) ? $matches[1] . $matches[2] : $matches[2]) . ' cfu/g' : 'N/A';

preg_match('/Moulds\s*(Less than\s*)?([\d.,]+)\s*cfu\/g/i', $pdfText, $matches);

$data['moulds'] = isset($matches[2]) ? (isset($matches[1]) ? $matches[1] . $matches[2] : $matches[2]) . ' cfu/g' : 'N/A';

dd($data);



// if (preg_match('/1-LPC.*?(LOQ \[Mol%\])/s', $pdfText, $blockMatches)) {
//     $block = $blockMatches[0];
//     echo "Captured Block:\n" . $block . "\n"; // Display the captured block
// } else {
//     echo "No block found!";
//     die();
// }

// // Split the block into fields
// $fields = preg_split('/\s+/', trim($block));

// // Debug: Output the fields with indices
// echo "Fields with indices:\n";
// foreach ($fields as $index => $field) {
//     echo "$index: $field\n";
// }

// // Correctly extract numeric values for 1-LPC
// $data['1LPC'] = [
//     'Integral' => $fields[18] ?? 'N/A',       
//     'MW [g/Mol]' => $fields[46] ?? 'N/A',    
//     'Content [mg]' => $fields[47] ?? 'N/A',  
//     'Mol-%' => $fields[48] ?? 'N/A',         
//     'Weight-%' => $fields[49] ?? 'N/A',      
// ];

// // Output extracted data
// echo "\nExtracted Data for 1-LPC:\n";
// print_r($data['1LPC']);
// die();











TestResults::create([
    'sample_number' => $data['sample_no'],
    'batch_number' => $data['batch'],
    'aceton_insoluble' => $data['aceton_insoluble'],
    'acid_value' => $data['acid_value'],
    'color_gardner' => $data['color_gardner'],
    'peroxide_value' => $data['peroxide_value'],
    'result_based_on_sample_mass' => $data['result_based_on_sample_mass'],
    'toluene_insoluble_matter' => $data['toluene_insoluble_matter'],
    'viscosity_25C' => $data['viscosity_25C'],
    'moisture' => $data['moisture'],
    'total_plate_count' => $data['total_plate_count'],
    'arsenic' => $data['arsenic'],
    'cadmium' => $data['cadmium'],
    'lead' => $data['lead'],
    'mercury' => $data['mercury'],
    'iron' => $data['iron'],
    'GMO_Screening' => $data['GMO Screening'],
    "enterobacteriaceae" =>$data['"enterobacteriaceae'] ,
    "yeasts_and_moulds" =>$data['yeasts_and_moulds'],
    "yeasts" =>$data['yeasts'],
    "moulds" =>$data['moulds'] 
]);
    return $data;
}


    
     
    

    
public function getTestDetails(){
    try {
        ob_clean(); 

        $test=TestResults::get();
        return response()->json([
            'ok'=>true,
            'status'=>"success",
            "messsage"=>"Tests retrieved successfully",
            "data"=>$test
        ]);
    } catch (\Throwable $th) {
        Log::error('Error processing PDF: ' . $th->getMessage());
        ob_clean();

        return response()->json([
            'ok' => false,
            'status' => 'error',
            'message' => 'Error occurred while processing the PDF.',
            'error' => $th->getMessage(),
        ], 500);
    }
}

public function getExcellDetails(Request $request){
    try {
        ob_clean();
        $searchQuery = $request->query('class');
        $test = Exports::where('red equivalent (if any; if not (certain): undefined)', $searchQuery)->paginate(5);
        return response()->json([
            'ok'=>true,
            'status'=>"success",
            "messsage"=>"Tests retrieved successfully",
            "data"=>$test
        ]);
    } catch (\Throwable $th) {
        Log::error('Error processing PDF: ' . $th->getMessage());
        ob_clean();

        return response()->json([
            'ok' => false,
            'status' => 'error',
            'message' => 'Error occurred while processing the PDF.',
            'error' => $th->getMessage(),
        ], 500);
    }
}



public function uploadExcell(ExcellRequest $request)
{
    try {
        $file = $request->file('file');
        $data = Excel::toArray([], $file);
        $sheetData = $data[0];

       $headers = array_map('strtolower', $sheetData[0]); 
       $columnMap = array_flip($headers); 
       foreach ($sheetData as $index => $row) {
           if ($index == 0) {
               continue; 
           }

           $parsedDate = null;
           if (!empty($row[$columnMap['date']] ?? null)) {
               try {
                   $parsedDate = Carbon::parse($row[$columnMap['date']])->format('Y-m-d');
               } catch (\Exception $e) {
                   Log::error('Date parsing error for row: ' . $index . ' with value: ' . $row[$columnMap['date']]);
               }
           }

           Exports::create([
               'hs_code' => $row[$columnMap['hs_code']] ?? null,
               'date' => $parsedDate,
               'product_description' => $row[$columnMap['product_description']] ?? null,
               'quantity' => $row[$columnMap['quantity']] ?? null,
               'unit' => $row[$columnMap['unit']] ?? null,
               'fob_value_usd' => $row[$columnMap['fob_value_usd']] ?? null,
               'indian_export_name' => $row[$columnMap['indian_exporter_name']] ?? null,
               'foreign_export_name' => $row[$columnMap['foreign_importer_name']] ?? null,
               'importer_country' => $row[$columnMap['importer_country']] ?? null,
           ]);
       }

        return response()->json(['message' => 'Excel file processed and data inserted successfully.','ok'=>true,'status'=>"sucesss"]);
    } catch (Throwable $th) {
        // Log the actual error message
        Log::error($th->getMessage());
        return response()->json(['error' => $th->getMessage()], 500);
    }
}

public function getExports()
{
    try {

        // Get paginated data from the 'exports' table
        $exports = Exports::paginate(10);

        // Return the paginated data as JSON response
        return response()->json($exports);
        
    } catch (Throwable $th) {
        // Log the error message for debugging
        Log::error('Error fetching exports: ' . $th->getMessage());

        // Return a JSON error response with status code 500
        return response()->json([
            'error' => 'Failed to retrieve exports data. Please try again later.'
        ], 500);
    }
}

public function uploadExcellExports(ExcellRequest $request)
{
    try {
       
        // Retrieve the uploaded file
        $file = $request->file('file');
      

        // Load data from the first sheet into an array
        $data = Excel::toArray([], $file);
        $sheetData = $data[0]; // Access the first sheet
        // Map the headers to lower case for uniformity
        $headers = array_map('strtolower', $sheetData[0]);
        $columnMap = array_flip($headers); // Maps header names to their index positions

        // Loop through each row of data, skipping the header row
        foreach ($sheetData as $index => $row) {
            if ($index == 0) {
                continue; // Skip the header row
            }
        
            $rowData = [];
        
            foreach ($columnMap as $columnName => $columnIndex) {
                if ($columnName == 'date' && !empty($row[$columnIndex] ?? null)) {
                    try {
                        $rowData[$columnName] = Carbon::parse($row[$columnIndex])->format('Y-m-d');
                    } catch (\Exception $e) {
                        Log::error('Date parsing error for row: ' . $index . ' with value: ' . $row[$columnIndex]);
                        $rowData[$columnName] = null;
                    }
                } else {
                    $rowData[$columnName] = $row[$columnIndex] ?? null;
                }
            }

           
            Exports::create($rowData);
        }
        

        // Return a success response
        return response()->json([
            'message' => 'Excel file processed and data inserted successfully.',
            'ok' => true,
            'status' => "success"
        ]);

    } catch (Throwable $th) {
        // Log the actual error message
        Log::error($th->getMessage());

        // Return an error response
        return response()->json([
            'ok' => false,
            'status' => 'error',
            'message' => 'Error occurred while processing the Excel file.',
            'error' => $th->getMessage(),
        ], 500);
    }
}




    
    
    
    
    
}