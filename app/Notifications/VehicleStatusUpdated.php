<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Vehicle;
use App\Models\User;

class VehicleStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $vehicle;
    protected $statusType;
    protected $oldStatus;
    protected $newStatus;
    protected $updatedBy;

    /**
     * Create a new notification instance.
     */
    public function __construct(Vehicle $vehicle, $statusType, $oldStatus, $newStatus, User $updatedBy)
    {
        $this->vehicle = $vehicle;
        $this->statusType = $statusType;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->updatedBy = $updatedBy;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('تم تحديث حالة عجلة')
                    ->line('تم تحديث حالة العجلة رقم: ' . $this->vehicle->id)
                    ->line('نوع العجلة: ' . $this->vehicle->vehicle_type)
                    ->line('رقم العجلة: ' . $this->vehicle->vehicle_number)
                    ->line('نوع التحديث: ' . $this->getStatusTypeName())
                    ->line('من: ' . $this->oldStatus)
                    ->line('إلى: ' . $this->newStatus)
                    ->line('تم التحديث بواسطة: ' . $this->updatedBy->name)
                    ->action('عرض العجلة', url('/vehicles/' . $this->vehicle->id));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'vehicle_id' => $this->vehicle->id,
            'vehicle_type' => $this->vehicle->vehicle_type,
            'vehicle_number' => $this->vehicle->vehicle_number,
            'status_type' => $this->statusType,
            'status_type_name' => $this->getStatusTypeName(),
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'updated_by' => $this->updatedBy->id,
            'updated_by_name' => $this->updatedBy->name,
            'updated_at' => now()->toDateTimeString(),
        ];
    }
    
    /**
     * Get the status type name in Arabic.
     */
    private function getStatusTypeName(): string
    {
        $statusTypes = [
            'seizure_status' => 'حالة المصادرة',
            'final_degree_status' => 'الدرجة القطعية',
            'valuation_status' => 'حالة التثمين',
            'authentication_status' => 'المصادقة',
            'donation_status' => 'الإهداء',
            'government_registration_status' => 'الترقيم الحكومي',
        ];
        
        return $statusTypes[$this->statusType] ?? $this->statusType;
    }
}