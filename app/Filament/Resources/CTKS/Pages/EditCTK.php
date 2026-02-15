<?php

namespace App\Filament\Resources\CTKS\Pages;

use App\Filament\Resources\CTKS\CTKResource;
use App\Filament\Resources\CTKS\Schemas\CTKForm;
use App\Filament\Resources\CTKS\Schemas\DocumentSection;
use App\Filament\Resources\CTKS\Schemas\MCUSection;
use App\Filament\Resources\CTKS\Schemas\MedicalFullSection;
use App\Filament\Resources\CTKS\Schemas\OPPSection;
use App\Filament\Resources\CTKS\Schemas\PaymentSection;
use App\Filament\Resources\CTKS\Schemas\ScreeningSection;
use App\Filament\Resources\CTKS\Schemas\TrainingSection;
use App\Filament\Resources\CTKS\Schemas\VisaSection;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class EditCTK extends EditRecord
{
    protected static string $resource = CTKResource::class;

    public function form(Schema $schema): Schema
    {
        $baseForm = CTKForm::configure($schema);

        // Get base components
        $baseComponents = $baseForm->getComponents();

        // Add stage-specific sections with status indicators
        $components = [
            ...$baseComponents,
            MCUSection::make(),
            PaymentSection::make(),
            DocumentSection::make(),
            TrainingSection::make(),
            ScreeningSection::make(),
            VisaSection::make(),
            MedicalFullSection::make(),
            OPPSection::make(),
        ];

        return $baseForm->components($components);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = Auth::id();

        // Handle MCU records - ensure created_by is set
        if (isset($data['mcuRecords'])) {
            foreach ($data['mcuRecords'] as &$mcuRecord) {
                if (! isset($mcuRecord['created_by'])) {
                    $mcuRecord['created_by'] = Auth::id();
                }
            }
        }

        // Handle Document records - ensure all required fields are set
        if (isset($data['documents'])) {
            foreach ($data['documents'] as &$document) {
                // Set uploader_id if not present
                if (! isset($document['uploader_id'])) {
                    $document['uploader_id'] = Auth::id();
                }

                // Set upload_timestamp if not present
                if (! isset($document['upload_timestamp'])) {
                    $document['upload_timestamp'] = now();
                }

                // Ensure filename is set from file_path
                if (isset($document['file_path'])) {
                    if (is_array($document['file_path']) && isset($document['file_path'][0])) {
                        $filePath = $document['file_path'][0];
                        $document['filename'] = $document['filename'] ?? basename($filePath);
                    } elseif (is_string($document['file_path'])) {
                        $document['filename'] = $document['filename'] ?? basename($document['file_path']);
                    }

                    // Set file_size if not present (use actual file size or default)
                    if (! isset($document['file_size'])) {
                        if (is_array($document['file_path']) && isset($document['file_path'][0])) {
                            $document['file_size'] = filesize(storage_path('app/public/'.$document['file_path'][0])) ?? 0;
                        } elseif (is_string($document['file_path'])) {
                            $document['file_size'] = filesize(storage_path('app/public/'.$document['file_path'])) ?? 0;
                        }
                    }

                    // Set mime_type if not present
                    if (! isset($document['mime_type'])) {
                        $mimeTypes = [
                            'pdf' => 'application/pdf',
                            'jpg' => 'image/jpeg',
                            'jpeg' => 'image/jpeg',
                            'png' => 'image/png',
                        ];
                        $ext = strtolower(pathinfo($document['filename'], PATHINFO_EXTENSION));
                        $document['mime_type'] = $mimeTypes[$ext] ?? 'application/octet-stream';
                    }
                }
            }
        }

        // Handle Training records - ensure created_by is set
        if (isset($data['trainings'])) {
            foreach ($data['trainings'] as &$training) {
                if (! isset($training['created_by'])) {
                    $training['created_by'] = Auth::id();
                }
            }
        }

        // Handle Screening records - ensure created_by is set
        if (isset($data['screenings'])) {
            foreach ($data['screenings'] as &$screening) {
                if (! isset($screening['created_by'])) {
                    $screening['created_by'] = Auth::id();
                }
            }
        }

        // Handle Medical Full records - ensure created_by is set
        if (isset($data['medicalFulls'])) {
            foreach ($data['medicalFulls'] as &$medicalFull) {
                if (! isset($medicalFull['created_by'])) {
                    $medicalFull['created_by'] = Auth::id();
                }
            }
        }

        return $data;
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Data CTK berhasil diperbarui';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
