<?php

namespace App\Filament\Resources\CTKS\Pages;

use App\Filament\Resources\CTKS\CTKResource;
use App\Filament\Resources\CTKS\Schemas\CTKForm;
use App\Filament\Resources\CTKS\Schemas\DocumentSection;
use App\Filament\Resources\CTKS\Schemas\MCUSection;
use App\Filament\Resources\CTKS\Schemas\MedicalFullSection;
use App\Filament\Resources\CTKS\Schemas\OPPSection;
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

        // Get existing components and add stage-specific sections
        // Note: Payments managed via PaymentsRelationManager on ViewCTK page
        $components = $baseForm->getComponents();
        $components[] = MCUSection::make();
        $components[] = DocumentSection::make();
        $components[] = TrainingSection::make();
        $components[] = ScreeningSection::make();
        $components[] = VisaSection::make();
        $components[] = MedicalFullSection::make();
        $components[] = OPPSection::make();

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

        // Payment records managed via PaymentsRelationManager

        // Handle Document records - ensure uploader_id and upload_timestamp are set
        if (isset($data['documents'])) {
            foreach ($data['documents'] as &$document) {
                if (! isset($document['uploader_id'])) {
                    $document['uploader_id'] = Auth::id();
                }
                if (! isset($document['upload_timestamp'])) {
                    $document['upload_timestamp'] = now();
                }
                // Extract file metadata if file_path is present
                if (isset($document['file_path']) && is_string($document['file_path'])) {
                    $document['filename'] = $document['filename'] ?? basename($document['file_path']);
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
