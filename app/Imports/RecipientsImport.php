<?php

namespace App\Imports;

use App\Models\RecipientGroup;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class RecipientsImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    public function __construct(private RecipientGroup $recipientGroup) {}

    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        $headers = $rows->first()->keys()->toArray();

        foreach ($rows as $row) {
            $rowArray = array_combine($headers, $row->toArray());
            $phoneNumber = $rowArray['phone_number'] ?? null;

            if (!$phoneNumber) {
                continue;
            }

            $phoneNumber = preg_replace('/\D/', '', $phoneNumber);

            $recipient = $this->recipientGroup->recipients()->create([
                'user_id' => $this->recipientGroup->user_id,
                'phone_number' => $phoneNumber
            ]);

            foreach ($rowArray as $label => $value) {
                if ($label === 'phone_number') {
                    continue;
                }

                // Attempt to convert numeric values to dates
                if (is_numeric($value)) {
                    try {
                        $value = Date::excelToDateTimeObject($value)->format('d-m-Y');
                    } catch (Exception $e) {
                        // If conversion fails, keep the original value
                    }
                }

                $isDateTime = $this->isFieldValidDateTime($value);


                Log::debug('Variable', [
                    'label' => $label,
                    'value' => $value,
                    'isDateTime' => $isDateTime,
                ]);

                $recipient->variables()->create([
                    'label' => trim($label),
                    'value' => trim($value),
                    'is_datetime' => $isDateTime,
                ]);
            }
        }
    }

    public function chunkSize(): int
    {
        return 500;
    }

    private function isFieldValidDateTime(string $field): bool
    {
        $date = null;
        try {
            Carbon::createFromFormat('d-m-Y H:i:s', $field);
            return true;
        } catch (Exception $e) {
            try {
                Carbon::createFromFormat('d-m-Y', $field);
                return true;
            } catch (Exception $e) {
                return false;
            }
        }
    }
}
