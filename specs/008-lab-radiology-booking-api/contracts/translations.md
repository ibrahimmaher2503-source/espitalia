# Translation Contracts: Lab & Radiology Booking API

**Feature Branch**: `008-lab-radiology-booking-api`
**Date**: 2026-04-02

## New Translation Keys

### lang/en/radiology_lab.php (additions)

```php
// Facility Booking
'booking_singular' => 'Booking',
'booking_plural' => 'Bookings',
'lbl_booking_number' => 'Booking Number',
'lbl_booking_type' => 'Booking Type',
'lbl_booking_date' => 'Booking Date',
'lbl_booking_time' => 'Booking Time',
'lbl_patient_name' => 'Patient Name',
'lbl_patient_phone' => 'Phone Number',
'lbl_notes' => 'Notes',
'lbl_admin_notes' => 'Admin Notes',
'lbl_cancellation_reason' => 'Cancellation Reason',

// Booking statuses
'status_pending' => 'Pending',
'status_confirmed' => 'Confirmed',
'status_cancelled' => 'Cancelled',
'status_completed' => 'Completed',
'status_no_show' => 'No Show',

// Booking types
'type_lab' => 'Lab',
'type_radiology' => 'Radiology',

// Success messages
'booking_created' => 'Booking created successfully.',
'booking_confirmed' => 'Booking confirmed successfully.',
'booking_cancelled' => 'Booking cancelled successfully.',
'booking_completed' => 'Booking marked as completed.',
'booking_no_show' => 'Booking marked as no-show.',

// Error messages
'slot_unavailable' => 'This time slot is no longer available.',
'facility_inactive' => 'This facility is currently inactive.',
'booking_not_cancellable' => 'This booking cannot be cancelled.',
'no_session_for_date' => 'No availability for this date.',
'past_date' => 'Cannot book for a past date.',
'invalid_transition' => 'This status change is not allowed.',

// Slots
'lbl_available_slots' => 'Available Slots',
'lbl_no_slots' => 'No available time slots for this date.',
```

### lang/ar/radiology_lab.php (additions)

```php
// Facility Booking
'booking_singular' => 'حجز',
'booking_plural' => 'الحجوزات',
'lbl_booking_number' => 'رقم الحجز',
'lbl_booking_type' => 'نوع الحجز',
'lbl_booking_date' => 'تاريخ الحجز',
'lbl_booking_time' => 'وقت الحجز',
'lbl_patient_name' => 'اسم المريض',
'lbl_patient_phone' => 'رقم الهاتف',
'lbl_notes' => 'ملاحظات',
'lbl_admin_notes' => 'ملاحظات الإدارة',
'lbl_cancellation_reason' => 'سبب الإلغاء',

// Booking statuses
'status_pending' => 'قيد الانتظار',
'status_confirmed' => 'مؤكد',
'status_cancelled' => 'ملغي',
'status_completed' => 'مكتمل',
'status_no_show' => 'لم يحضر',

// Booking types
'type_lab' => 'مختبر',
'type_radiology' => 'أشعة',

// Success messages
'booking_created' => 'تم إنشاء الحجز بنجاح.',
'booking_confirmed' => 'تم تأكيد الحجز بنجاح.',
'booking_cancelled' => 'تم إلغاء الحجز بنجاح.',
'booking_completed' => 'تم وضع علامة مكتمل على الحجز.',
'booking_no_show' => 'تم تسجيل عدم الحضور.',

// Error messages
'slot_unavailable' => 'هذا الموعد لم يعد متاحاً.',
'facility_inactive' => 'هذا المرفق غير نشط حالياً.',
'booking_not_cancellable' => 'لا يمكن إلغاء هذا الحجز.',
'no_session_for_date' => 'لا توجد مواعيد متاحة لهذا التاريخ.',
'past_date' => 'لا يمكن الحجز في تاريخ ماضٍ.',
'invalid_transition' => 'هذا التغيير في الحالة غير مسموح.',

// Slots
'lbl_available_slots' => 'المواعيد المتاحة',
'lbl_no_slots' => 'لا توجد مواعيد متاحة لهذا التاريخ.',
```
