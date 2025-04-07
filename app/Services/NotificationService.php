<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Notification;
use Spatie\Activitylog\Models\Activity;

class NotificationService
{
    /**
     * Process activity and send notifications
     *
     * @param Activity $activity
     * @return void
     */
    public function processActivity(Activity $activity)
    {
        $subject = $activity->subject;

        // لا نرسل إشعارات إذا كان الكائن غير موجود
        if (!$subject) {
            return;
        }

        // Determine which type of activity this is
        if ($subject instanceof \App\Models\Vehicle) {
            $this->processVehicleActivity($activity, $subject);
        } elseif ($subject instanceof \App\Models\VehicleTransfer) {
            $this->processTransferActivity($activity, $subject);
        } elseif ($subject instanceof \App\Models\EditRequest) {
            $this->processEditRequestActivity($activity, $subject);
        }
    }

    /**
     * Process Vehicle related activity
     */
    protected function processVehicleActivity(Activity $activity, $vehicle)
    {
        $eventName = $activity->event;
        $usersToNotify = $this->getRelevantUsers($vehicle, $eventName);
    
        // استخدم الوصف المحدد مباشرة من النشاط
        $description = $activity->description;
    
        // حدد الرسالة بناءً على نوع الحدث
        $message = '';
        if ($eventName == 'created') {
            $typeText = $vehicle->type == 'confiscated' ? 'مصادرة' : 'حكومية';
            $message = 'تمت إضافة عجلة ' . $typeText . ' جديدة نوع ' . $vehicle->vehicle_type;
            if ($vehicle->vehicle_number) {
                $message .= ' رقم ' . $vehicle->vehicle_number;
            }
        } elseif ($eventName == 'updated') {
            // Check for status changes
            if (!empty($activity->properties['attributes']) && !empty($activity->properties['old'])) {
                foreach ($activity->properties['attributes'] as $key => $newValue) {
                    if (in_array($key, ['seizure_status', 'final_degree_status', 'valuation_status', 'authentication_status', 'donation_status', 'government_registration_status'])
                        && isset($activity->properties['old'][$key])
                        && $activity->properties['old'][$key] != $newValue) {
                        
                        $message = 'تم تغيير ' . $this->getStatusTypeName($key) . ' للعجلة ' . $vehicle->vehicle_type;
                        if ($vehicle->vehicle_number) {
                            $message .= ' رقم ' . $vehicle->vehicle_number;
                        }
                        $message .= ' من ' . $activity->properties['old'][$key] . ' إلى ' . $newValue;
                        break;
                    }
                }
            }
        
            // If no specific status message was set
            if (empty($message)) {
                $message = 'تم تحديث بيانات العجلة ' . $vehicle->vehicle_type;
                if ($vehicle->vehicle_number) {
                    $message .= ' رقم ' . $vehicle->vehicle_number;
                }
            }
        }
    
        $notificationData = [
            'title' => $description,
            'message' => $message,
            'icon' => 'bi bi-truck',
            'vehicle_id' => $vehicle->id,
            'vehicle_type' => $vehicle->vehicle_type,
            'vehicle_number' => $vehicle->vehicle_number,
            'vehicle_name' => $vehicle->vehicle_name,
            'action_url' => route('vehicles.show', $vehicle->id),
            'causer_id' => $activity->causer_id,
            'causer_name' => $activity->causer ? $activity->causer->name : 'النظام',
            'created_at' => $activity->created_at
        ];
    
        // Add properties for status changes
        if ($eventName == 'updated' && !empty($activity->properties['attributes']) && !empty($activity->properties['old'])) {
            foreach ($activity->properties['attributes'] as $key => $newValue) {
                if (in_array($key, ['seizure_status', 'final_degree_status', 'valuation_status', 'authentication_status', 'donation_status', 'government_registration_status'])
                    && isset($activity->properties['old'][$key])
                    && $activity->properties['old'][$key] != $newValue) {
                    
                    $notificationData['status_type'] = $key;
                    $notificationData['status_type_name'] = $this->getStatusTypeName($key);
                    $notificationData['old_status'] = $activity->properties['old'][$key];
                    $notificationData['new_status'] = $newValue;
                    break;
                }
            }
        }
    
        Notification::send($usersToNotify, new DatabaseNotification($notificationData));
    }

    /**
     * Process VehicleTransfer related activity
     */
    protected function processTransferActivity(Activity $activity, $transfer)
    {
        $eventName = $activity->event;
        $usersToNotify = $this->getRelevantUsers($transfer, $eventName);

        // استخدم الوصف المحدد مباشرة من النشاط
        $description = $activity->description;

        // Determine transfer type
        $transferType = '';
        if ($transfer->is_ownership_transfer) {
            $transferType = 'نقل ملكية';
        } elseif ($transfer->is_referral) {
            $transferType = 'إحالة خارجية';
        } else {
            $transferType = 'مناقلة';
        }

        // بناء الرسالة بناء على نوع الحدث
        $message = '';
        if ($eventName == 'created') {
            if ($transfer->is_ownership_transfer) {
                $message = 'تم نقل ملكية العجلة ' . $transfer->vehicle->vehicle_type;
            } elseif ($transfer->is_referral) {
                $message = 'تمت إحالة العجلة ' . $transfer->vehicle->vehicle_type . ' خارجيًا';
            } else {
                $message = 'تمت مناقلة العجلة ' . $transfer->vehicle->vehicle_type;
            }

            if ($transfer->vehicle->vehicle_number) {
                $message .= ' رقم ' . $transfer->vehicle->vehicle_number;
            }

            $message .= ' إلى ' . $transfer->recipient_entity;
        }
        elseif ($eventName == 'updated' && isset($activity->properties['attributes']['return_date']) && !is_null($activity->properties['attributes']['return_date'])) {
            $message = 'تم إكمال مناقلة العجلة ' . $transfer->vehicle->vehicle_type;
            if ($transfer->vehicle->vehicle_number) {
                $message .= ' رقم ' . $transfer->vehicle->vehicle_number;
            }
        }

        $notificationData = [
            'title' => $description,
            'message' => $message,
            'icon' => 'bi bi-arrow-left-right',
            'transfer_id' => $transfer->id,
            'vehicle_id' => $transfer->vehicle->id,
            'vehicle_type' => $transfer->vehicle->vehicle_type,
            'vehicle_number' => $transfer->vehicle->vehicle_number,
            'recipient_name' => $transfer->recipient_name,
            'recipient_entity' => $transfer->recipient_entity,
            'transfer_type' => $transferType,
            'transfer_date' => $transfer->receive_date->format('Y-m-d'),
            'action_url' => route('transfers.show', $transfer->id),
            'causer_id' => $activity->causer_id,
            'causer_name' => $activity->causer ? $activity->causer->name : 'النظام',
            'created_at' => $activity->created_at
        ];

        if ($eventName == 'updated' && isset($activity->properties['attributes']['return_date']) && !is_null($activity->properties['attributes']['return_date'])) {
            $notificationData['return_date'] = $transfer->return_date->format('Y-m-d');
        }

        Notification::send($usersToNotify, new DatabaseNotification($notificationData));
    }

    /**
     * Process EditRequest related activity
     */
    protected function processEditRequestActivity(Activity $activity, $editRequest)
    {
        $eventName = $activity->event;
        $usersToNotify = $this->getRelevantUsers($editRequest, $eventName);

        // استخدم الوصف المحدد مباشرة من النشاط
        $description = $activity->description;

        // بناء الرسالة بناء على نوع الحدث
        $message = '';
        if ($eventName == 'created') {
            $message = 'تم إنشاء طلب لتعديل حقل ' . $editRequest->field_name . ' للعجلة ' . $editRequest->vehicle->vehicle_type;
            if ($editRequest->vehicle->vehicle_number) {
                $message .= ' رقم ' . $editRequest->vehicle->vehicle_number;
            }
        }
        elseif ($eventName == 'updated' &&
                isset($activity->properties['attributes']['status']) &&
                $activity->properties['attributes']['status'] != 'pending') {

            if ($activity->properties['attributes']['status'] == 'approved') {
                $message = 'تمت الموافقة على طلب تعديل حقل ' . $editRequest->field_name . ' للعجلة ' . $editRequest->vehicle->vehicle_type;
            } else {
                $message = 'تم رفض طلب تعديل حقل ' . $editRequest->field_name . ' للعجلة ' . $editRequest->vehicle->vehicle_type;
            }

            if ($editRequest->vehicle->vehicle_number) {
                $message .= ' رقم ' . $editRequest->vehicle->vehicle_number;
            }
        }

        $notificationData = [
            'title' => $description,
            'message' => $message,
            'icon' => 'bi bi-pencil-square',
            'edit_request_id' => $editRequest->id,
            'vehicle_id' => $editRequest->vehicle_id,
            'vehicle_type' => $editRequest->vehicle->vehicle_type,
            'vehicle_name' => $editRequest->vehicle->vehicle_name,
            'vehicle_number' => $editRequest->vehicle->vehicle_number,
            'field_name' => $editRequest->field_name,
            'old_value' => $editRequest->old_value,
            'new_value' => $editRequest->new_value,
            'status' => $editRequest->status,
            'action_url' => route('edit-requests.show', $editRequest->id),
            'causer_id' => $activity->causer_id,
            'causer_name' => $activity->causer ? $activity->causer->name : 'النظام',
            'created_at' => $activity->created_at
        ];

        Notification::send($usersToNotify, new DatabaseNotification($notificationData));
    }

    /**
     * Get users who should be notified based on the entity and event
     */
    protected function getRelevantUsers($entity, $eventName)
    {
        $usersToNotify = [];

        // Always notify admins and verifiers
        $adminUsers = User::role(['admin', 'verifier'])->get();
        $adminUserIds = $adminUsers->pluck('id')->toArray();

        foreach ($adminUsers as $adminUser) {
            $usersToNotify[] = $adminUser;
        }

        if ($entity instanceof \App\Models\Vehicle) {
            // If it's a vehicle, also notify users from the same directorate
            if ($entity->directorate) {
                $directoryUsers = User::where('directorate_id', $entity->directorate_id)
                                    ->whereNotIn('id', $adminUserIds)
                                    ->get();
                foreach ($directoryUsers as $dirUser) {
                    $usersToNotify[] = $dirUser;
                }
            }

            // For certain statuses, notify the vehicles department
            if ($eventName == 'updated' &&
                ($entity->final_degree_status == 'مكتسبة' ||
                 $entity->authentication_status == 'تمت المصادقة عليها')) {

                $vehiclesDeptUsers = User::role('vehicles_dept')
                                        ->whereNotIn('id', collect($usersToNotify)->pluck('id')->toArray())
                                        ->get();
                foreach ($vehiclesDeptUsers as $vdUser) {
                    $usersToNotify[] = $vdUser;
                }
            }
        }
        elseif ($entity instanceof \App\Models\VehicleTransfer) {
            // For transfers, notify vehicles dept
            $vehiclesDeptUsers = User::role('vehicles_dept')
                                    ->whereNotIn('id', $adminUserIds)
                                    ->get();
            foreach ($vehiclesDeptUsers as $vdUser) {
                $usersToNotify[] = $vdUser;
            }

            // If it's a transfer to a directorate, notify recipients from that directorate
            if ($entity->destination_directorate_id) {
                $recipientUsers = User::role('recipient')
                    ->where('directorate_id', $entity->destination_directorate_id)
                    ->whereNotIn('id', collect($usersToNotify)->pluck('id')->toArray())
                    ->get();
                foreach ($recipientUsers as $recipientUser) {
                    $usersToNotify[] = $recipientUser;
                }
            }
        }
        elseif ($entity instanceof \App\Models\EditRequest) {
            // For edit requests, only notify the requester and admins/verifiers
            if ($eventName == 'updated' && $entity->user_id && !in_array($entity->user_id, collect($usersToNotify)->pluck('id')->toArray())) {
                $requester = User::find($entity->user_id);
                if ($requester) {
                    $usersToNotify[] = $requester;
                }
            }
        }

        // Remove duplicates and return
        return collect($usersToNotify)->unique('id')->values()->all();
    }

    /**
     * Get the status type name in Arabic.
     */
    private function getStatusTypeName($statusType)
    {
        $statusTypes = [
            'seizure_status' => 'حالة المصادرة',
            'final_degree_status' => 'الدرجة القطعية',
            'valuation_status' => 'حالة التثمين',
            'authentication_status' => 'المصادقة',
            'donation_status' => 'الإهداء',
            'government_registration_status' => 'الترقيم الحكومي',
        ];

        return $statusTypes[$statusType] ?? $statusType;
    }
}
