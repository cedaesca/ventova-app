<?php

namespace App\Jobs;

use App\Imports\RecipientsImport;
use App\Models\RecipientGroup;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ProcessRecipientExcel implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(private string $filePath, private int $recipientGroupId) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $recipientGroup = RecipientGroup::find($this->recipientGroupId);

        if (!$recipientGroup) {
            Log::error("Recipient group with ID {$this->recipientGroupId} not found.");
            return;
        }

        $recipientGroup->is_importing = true;
        $recipientGroup->save();

        try {
            Log::info("Starting to process recipient group ID {$this->recipientGroupId}");

            Excel::import(new RecipientsImport($recipientGroup), $this->filePath, 'local');

            Storage::disk('local')->delete($this->filePath);
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();

            Log::error("Error importing recipients for group ID {$this->recipientGroupId}: {$errorMessage}");

            throw $e;
        } finally {
            $recipientGroup->is_importing = false;
            $recipientGroup->save();
            Log::info("Finished processing recipient group ID {$this->recipientGroupId}");
        }
    }
}
