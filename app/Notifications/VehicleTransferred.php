<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\VehicleTransfer;
use App\Models\User;

class VehicleTransferred extends Notification implements ShouldQueue
{
    use Queueable;

    protected $transfer;
    protected $user;

    /**
     * Create a new notification instance.
     */
    public function __construct(VehicleTransfer $transfer, User $user)
    {
        $this->transfer = $transfer;
        $this->user = $user;
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
        $transferType = $this->getTransferType();
        
        return (new MailMessage)
                    ->subject($transferType . ' عجلة')
                    ->line('تم ' . $transferType . ' العجلة رقم: ' . $this->transfer->vehicle->id)
                    ->line('نوع العجلة: ' . $this->transfer->vehicle->vehicle_type)
                    ->line('رقم العجلة: ' . $this->transfer->vehicle->vehicle_number)
                    ->line('المستلم: ' . $this->transfer->recipient_name)
                    ->line('الجهة المستلمة: ' . $this->transfer->recipient_entity)
                    ->line('تاريخ الاستلام: ' . $this->transfer->receive_date->format('Y-m-d'))
                    ->line('تم بواسطة: ' . $this->user->name)
                    ->action('عرض تفاصيل المناقلة', url('/transfers/' . $this->transfer->id));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $transferType = $this->getTransferType();
        
        return [
            'transfer_id' => $this->transfer->id,
            'vehicle_id' => $this->transfer->vehicle->id,
            'vehicle_type' => $this->transfer->vehicle->vehicle_type,
            'vehicle_number' => $this->transfer->vehicle->vehicle_number,
            'recipient_name' => $this->transfer->recipient_name,
            'recipient_entity' => $this->transfer->recipient_entity,
            'transfer_type' => $transferType,
            'transfer_date' => $this->transfer->receive_date->format('Y-m-d'),
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
        ];
    }
    
    /**
     * Get transfer type in Arabic.
     */
    private function getTransferType(): string
    {
        if ($this->transfer->is_ownership_transfer) {
            return 'نقل ملكية';
        } elseif ($this->transfer->is_referral) {
            return 'إحالة خارجية';
        } else {
            return 'مناقلة';
        }
    }
}