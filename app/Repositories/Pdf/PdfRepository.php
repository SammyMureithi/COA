<?php

namespace App\Repositories\Pdf;

use App\Http\Requests\ExcellRequest;
use App\Http\Requests\PdfQuestioneer;
use App\Models\ExportData;
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
            // Check if files are uploaded
            if ($request->hasFile('file')) {
                $files = $request->file('file'); // This can either be a single file or an array of files
    
                // Ensure that $files is an array
                if (!is_array($files)) {
                    $files = [$files];  // Convert single file to an array
                }
    
                // Debug: Check if files exist
                Log::info('Files found: ' . count($files));
    
                $filePaths = []; // Store paths of successfully uploaded files
    
                foreach ($files as $file) {
                    // Debug: Log the file information
                    Log::info('Processing file: ' . $file->getClientOriginalName());
    
                    // Sanitize the file name
                    $originalFileName = $file->getClientOriginalName();
                    $sanitizedFileName = preg_replace('/[^A-Za-z0-9\-_\.]/', '_', str_replace(' ', '_', $originalFileName));
    
                    // Store the uploaded file using the Storage facade
                    $path = Storage::disk('local')->putFileAs('private/pdfs', $file, $sanitizedFileName);
                    $filePaths[] = storage_path('app/' . $path);
    
                    // Extract text from the PDF
                    $pdfText = Pdf::getText(storage_path('app/' . $path));
    
                    // Parse the relevant fields from the PDF content
                    $data = $this->extractPdfData($pdfText);
                    echo "hekksond";
                }
    
                // Clear any previous output buffer
                ob_clean();
    
                return response()->json([
                    'ok' => true,
                    'status' => 'success',
                    'message' => 'All files processed successfully',
                ]);
            } else {
                Log::error('No files found in the request.');
                return response()->json([
                    'ok' => false,
                    'status' => 'error',
                    'message' => 'No PDF files found in the request.',
                ], 400);
            }
        } catch (\Throwable $th) {
            Log::error('Error processing PDFs: ' . $th->getMessage());
    
            // Clear any previous output buffer
            ob_clean();
    
            return response()->json([
                'ok' => false,
                'status' => 'error',
                'message' => 'Error occurred while processing the PDFs.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
    
    
private function extractPdfData($pdfText){
    // Remove excessive newlines to handle multi-line values better
    $pdfText = preg_replace("/\n+/", " ", $pdfText);
    echo "hakikmd";
    // Initialize data array
    $data = [
        'sample_no' => null,
        'batch' =>  null,
        'aceton_insoluble' =>  null,
        'acid_value' =>  null,
        'color_gardner'=>  null,
        'peroxide_value' =>  null,
        'result_based_on_sample_mass' =>  null,
        'toluene_insoluble_matter'=>  null,
        'viscosity_25C' => null,
        'lpc'=>null,
        'phosphorous'=>null
    ];
    echo "hakikmd";

    
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
echo "hakikmd";
   // Extract "Aceton insoluble" value
preg_match('/Aceton insoluble\s*([\d,]+)\s*%/i', $pdfText, $matches);

// Store only the numeric value without the unit
$data['aceton_insoluble'] = isset($matches[1]) ? $matches[1] : null;

  
      // Extract "Acid value" value
preg_match('/Acid value\s*([\d,]+)\s*mg KOH\/g/i', $pdfText, $matches);

// Store only the numeric value without the unit
$data['acid_value'] = isset($matches[1]) ? $matches[1] : null;


     // Extract "Color Gardner, dilution 10 (w/w) with toluene" value
     preg_match('/Color Gardner, dilution 10 \(w\/w\) with toluene\s*([\d,]+)/i', $pdfText, $matches);
     $data['color_gardner'] = isset($matches[1]) ? $matches[1] : null;

   // Extract "Peroxide value" value
if (preg_match('/Peroxide value\s*(Less than\s*)?([\d,]+)\s*meq O2\/kg/i', $pdfText, $matches)) {
    // Check if "Less than" is present
    $data['peroxide_value'] = isset($matches[1]) && trim($matches[1]) === 'Less than'
        ? 'Less than ' . $matches[2] // Return only the numeric value with "Less than" if applicable
        : $matches[2]; // Just the numeric value without units
} else {
    $data['peroxide_value'] = null; // Return null if no match
}

    if (preg_match('/Result based on sample mass of\s*([\d,]+)\s*(\w+)/i', $pdfText, $matches)) {
        $data['result_based_on_sample_mass'] = $matches[1] . ' ' . $matches[2];
    }
  // Extract "Toluene insoluble matter" value
preg_match('/Toluene insoluble matter\s*([\d,]+)\s*%/i', $pdfText, $matches);

// Store only the numeric value without the unit
$data['toluene_insoluble_matter'] = isset($matches[1]) ? $matches[1] : null;


  // Extract "Viscosity at 25°C" value
if (preg_match('/Viscosity at 25°C\s*([\d,]+)\s*(\w+)?/i', $pdfText, $matches)) {
    // Get the numeric part, remove commas if present
    $numericValue = (int) str_replace(',', '', $matches[1]);

    // Divide the value by 1000 and store it as a float
    $data['viscosity_25C'] = number_format($numericValue / 1000, 3, '.', '');
}


    if (!isset($data['batch']) || $data['batch'] === "N/A") {
        // Adjusted regex to capture batch number
        preg_match('/Batch\s*number.*?\b(B[A-Z0-9]+)/i', $pdfText, $matches);
        $data['batch'] = isset($matches[1]) ? $matches[1] :  null;
        
       
    }
// Extract "Moisture" value
preg_match('/Moisture\s*([\d.,]+)\s*%\s*\(w\/w\)/i', $pdfText, $matches);

// Store only the numeric value without the unit
$data['moisture'] = isset($matches[1]) ? $matches[1] : null;


// Regex to match "Total plate count 30°C" followed by a numeric value and "cfu/g" unit
preg_match('/Total plate count 30°C\s*([\d,]+)\s*cfu\/g/i', $pdfText, $matches);

// Extract "Total plate count" value
if (preg_match('/Total plate count\s*([\d,]+)\s*cfu\/g/i', $pdfText, $matches)) {
    // Return only the numeric value without the unit "cfu/g"
    $data['total_plate_count'] = $matches[1]; 
} else {
    $data['total_plate_count'] = null; // Return null if no match
}


// Regex to capture "Arsenic (As) (7440-38-2)" and the value following it
// General regex to capture "Arsenic (As) (7440-38-2)" with variable values
preg_match('/Arsenic \(As\) \(7440-38-2\)\s*(Less than\s*)?([\d.,]+)\s*mg\/kg/i', $pdfText, $matches);

$data['arsenic'] = isset($matches[2]) ? (isset($matches[1]) ? $matches[1] . $matches[2] : $matches[2]) . ' mg/kg' :  null;

preg_match('/(Arsenic \(As\) \(7440-38-2\)|Cadmium \(Cd\) \(7440-43-9\))\s*(Less than\s*)?([\d.,]+)\s*mg\/kg/i', $pdfText, $matches);

// Regex to capture "Cadmium (Cd) (7440-43-9)" with any associated value
preg_match('/Cadmium \(Cd\) \(7440-43-9\)\s*(Less than\s*)?([\d.,]+)\s*mg\/kg/i', $pdfText, $matches);

$data['cadmium'] = isset($matches[2]) ? (isset($matches[1]) ? $matches[1] . $matches[2] : $matches[2]) . ' mg/kg' :  null;
// Regex to capture "Lead (Pb) (7439-92-1)" with any associated value
preg_match('/Lead \(Pb\) \(7439-92-1\)\s*(Less than\s*)?([\d.,]+)\s*mg\/kg/i', $pdfText, $matches);

$data['lead'] = isset($matches[2]) ? (isset($matches[1]) ? $matches[1] . $matches[2] : $matches[2]) . ' mg/kg' :  null;
preg_match('/Mercury \(Hg\) \(7439-97-6\)\s*(Less than\s*)?([\d.,]+)\s*mg\/kg/i', $pdfText, $matches);

$data['mercury'] = isset($matches[2]) ? (isset($matches[1]) ? $matches[1] . $matches[2] : $matches[2]) . ' mg/kg' :  null;
// Regex to capture "Total plate count 30°C" followed by a numeric value and "cfu/g"
preg_match('/Total plate count 30°C\s*([\d.,]+)\s*cfu\/g/i', $pdfText, $matches);

$data['total_plate_count'] = isset($matches[1]) ? $matches[1] . ' cfu/g' :  null;

// Regex to capture "Iron (Fe) (7439-89-6)" with its associated value
preg_match('/Iron \(Fe\) \(7439-89-6\)\s*(Less than\s*)?([\d.,]+)\s*mg\/kg/i', $pdfText, $matches);

$data['iron'] = isset($matches[2]) ? (isset($matches[1]) ? $matches[1] . $matches[2] : $matches[2]) : null;

// Regex to capture "GMO Screening" test result (either 'positive' or 'negative')
preg_match('/GMO Screening.*?\b(positive|negative)\b/i', $pdfText, $matches);

$data['GMO Screening'] = isset($matches[1]) ? $matches[1] : null;

preg_match('/Enterobacteriaceae\s*(Less than\s*)?([\d.,]+)\s*cfu\/g/i', $pdfText, $matches);

$data['enterobacteriaceae'] = isset($matches[2]) ? (isset($matches[1]) ? $matches[1] . $matches[2] : $matches[2]) . ' cfu/g' :  null;
 
preg_match('/Total plate count\s*30°C\s*([\d.,]+)\s*cfu\/g/i', $pdfText, $matches);

$data['total_plate_count'] = isset($matches[1]) ? $matches[1] . ' cfu/g' :  null;

preg_match('/Yeasts\s*&\s*moulds\s*(Less than\s*)?([\d.,]+)\s*cfu\/g/i', $pdfText, $matches);

// Check for Yeasts and Moulds
preg_match('/Yeasts\s*(Less than\s*)?([\d.,]+)\s*cfu\/g/i', $pdfText, $matches);
$data['yeasts_and_moulds'] = isset($matches[2]) ? (isset($matches[1]) ? $matches[1] . $matches[2] : $matches[2]) . ' cfu/g' : null;
if ($data['yeasts_and_moulds'] === 'Less than 10 cfu/g') {
    $data['yeasts_and_moulds'] = 'Negative';
}

// Check for Yeasts
preg_match('/Yeasts\s*(Less than\s*)?([\d.,]+)\s*cfu\/g/i', $pdfText, $matches);
$data['yeasts'] = isset($matches[2]) ? (isset($matches[1]) ? $matches[1] . $matches[2] : $matches[2]) . ' cfu/g' : null;
if ($data['yeasts'] === 'Less than 10 cfu/g') {
    $data['yeasts'] = 'Negative';
}

// Check for Moulds
preg_match('/Moulds\s*(Less than\s*)?([\d.,]+)\s*cfu\/g/i', $pdfText, $matches);
$data['moulds'] = isset($matches[2]) ? (isset($matches[1]) ? $matches[1] . $matches[2] : $matches[2]) . ' cfu/g' : null;
if ($data['moulds'] === 'Less than 10 cfu/g') {
    $data['moulds'] = 'Negative';
}


// ******************************

$pdfText = nl2br(htmlspecialchars($pdfText)); 
// Find the position of "Phospholipid" and "1-LPC"
$phospholipid_pos = strpos($pdfText, "Phospholipid"); // Position of "Phospholipid"
$lpc_pos = strpos($pdfText, "1-LPC"); 
$lpc_2_pos = strpos($pdfText, "2-LPC"); 
$phosp_pos= strpos($pdfText, "Phosphorus");
if($lpc_pos != false){
// Extract text between "Phospholipid" and "1-LPC"
$section_1 = substr($pdfText, $phospholipid_pos, $lpc_pos - $phospholipid_pos);

// Extract text between "Phospholipid" and "2-LPC"
$section_2 = substr($pdfText, $phospholipid_pos, $lpc_2_pos - $phospholipid_pos);

// Extract text between "Phospholipid" and "2-LPC"
$section_3 = substr($pdfText, $phospholipid_pos, $phosp_pos - $phospholipid_pos);

// Split by spaces or delimiters to count the words/steps for 1-LPC
$steps_1 = str_word_count($section_1, 1);

// Split by spaces or delimiters to count the words/steps for 2-LPC
$steps_2 = str_word_count($section_2, 1);

// Split by spaces or delimiters to count the words/steps for 2-LPC
$steps_3 = str_word_count($section_3, 1);

// Count steps from "Phospholipid" to "1-LPC"
$stepCount_1 = count($steps_1);

// Count steps from "Phospholipid" to "2-LPC"
$stepCount_2 = count($steps_2);

$stepCount_3 = count($steps_3);

// Now, we need to locate Weight-% and move the number of steps ahead to find the corresponding value for 1-LPC and 2-LPC

// Find the position of "Weight-%"
$weight_pos = strpos($pdfText, "Weight-%");

// Extract the text after "Weight-%"
$weight_section = substr($pdfText, $weight_pos);

// Split this section to extract all the numbers in the Weight-% section
$weight_values = preg_split('/\s+/', $weight_section);

// Adjust the step count by subtracting 1 (since the first entry is "Weight-%")
$adjusted_index_1 = $stepCount_1 - 1;
$adjusted_index_2 = $stepCount_2 - 1;
$adjusted_index_3 = $stepCount_3 - 1;

// Get the values for 1-LPC and 2-LPC at the adjusted indexes
$target_weight_1lpc = $weight_values[2]; 
$target_weight_2lpc = $weight_values[3]; 
$target_weight_phosphorous = $weight_values[15]; 
$data['lpc'] = (isset($target_weight_1lpc) && isset($target_weight_2lpc))
? $target_weight_1lpc + $target_weight_2lpc
: null;

// Use the same logic to combine Phosphorus weight into $data['phosphorous']
$data['phosphorous'] = isset($target_weight_phosphorous) ? $target_weight_phosphorous : null;

}
// ***********************

// $pattern = '/Benzo\(a\)anthracene\s*\(56-55-3\)\s*[:\-]?\s*([^;]*?)(?=\s*;|$)/i';


// preg_match($pattern, $pdfText, $matches);

// $data = [];

// if (isset($matches[1])) {
//     $data['benzo_a_anthracene'] = trim($matches[1]);  
// } else {
//     $data['benzo_a_anthracene'] = null;  
// }




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
    "enterobacteriaceae" =>$data['enterobacteriaceae'] ,
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

public function getExcellDetails(Request $request)
{
    try {
        ob_clean();

        // Paginate the ExportData records (5 per page)
        $exports = ExportData::paginate(5);

        return response()->json([
            'ok' => true,
            'status' => "success",
            "message" => "Exports retrieved successfully",
            "data" => $exports
        ]);
    } catch (\Throwable $th) {
        Log::error('Error retrieving ExportData: ' . $th->getMessage());
        ob_clean();

        return response()->json([
            'ok' => false,
            'status' => 'error',
            'message' => 'Error occurred while retrieving the export details.',
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

        // Map the headers to lowercase for uniformity
        $headers = array_map('strtolower', $sheetData[0]);
        $columnMap = array_flip($headers); // Maps header names to their index positions

        // Loop through each row of data, skipping the header row
        foreach ($sheetData as $index => $row) {
            if ($index == 0) {
                continue; // Skip the header row
            }

            $rowData = [];

            foreach ($columnMap as $columnName => $columnIndex) {
                // Parse date fields
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

            // Check for a close match in the Export table using the product_description
            $productDescription = $rowData['product_description'] ?? null;

            if (!empty($productDescription)) {
                // Perform a similarity check in the database
                $similarRecord = Exports::query()
                    ->where(function ($query) use ($productDescription) {
                        $query->whereRaw('SOUNDEX(product_description) = SOUNDEX(?)', [$productDescription])
                            ->orWhere('product_description', 'LIKE', '%' . $productDescription . '%');
                    })
                    ->first();

                if ($similarRecord) {
                    // If a match is found, log it and insert the record into ExportData
                    Log::info('Matched: ' . $productDescription . ' with ' . $similarRecord->product_description);
                  
                    ExportData::create($rowData);
                } else {
                    // If no match is found, optionally perform a custom similarity check
                    $allRecords = Exports::all(); // Load all records for comparison
                    $bestMatch = null;
                    $highestSimilarity = 0;

                    foreach ($allRecords as $record) {
                        similar_text($productDescription, $record->product_description, $percent);

                        if ($percent > $highestSimilarity) {
                            $highestSimilarity = $percent;
                            $bestMatch = $record;
                        }
                    }

                    // If similarity is above a threshold, consider it a match
                    if ($highestSimilarity > 50) { // Threshold (50%) can be adjusted as needed
                        Log::info('Fuzzy Match: ' . $productDescription . ' with ' . $bestMatch->product_description . ' (Similarity: ' . $highestSimilarity . '%)');
                        ExportData::create($rowData);
                    } else {
                        Log::warning('No close match found for: ' . $productDescription);
                    }
                }
            } else {
                Log::warning('No product description found for row: ' . $index);
            }
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