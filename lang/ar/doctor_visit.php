<?php

return [
    // Page titles
    'singular_title' => 'طلب زيارة طبيب',
    'plural_title'   => 'طلبات زيارة الأطباء',

    // Field labels
    'lbl_reference_number'    => 'الرقم المرجعي',
    'lbl_patient_name'        => 'اسم المريض',
    'lbl_preferred_doctor'    => 'الطبيب المفضل',
    'lbl_assigned_doctor'     => 'الطبيب المعين',
    'lbl_visit_type'          => 'نوع الزيارة',
    'lbl_visit_reason'        => 'سبب الزيارة',
    'lbl_preferred_date'      => 'التاريخ المفضل',
    'lbl_contact_phone'       => 'هاتف التواصل',
    'lbl_additional_notes'    => 'ملاحظات إضافية',
    'lbl_status'              => 'الحالة',
    'lbl_admin_notes'         => 'ملاحظات الإدارة',
    'lbl_cancellation_reason' => 'سبب الإلغاء',
    'lbl_completed_at'        => 'تاريخ الإكمال',
    'lbl_action'              => 'الإجراء',

    // Status labels
    'status_pending'   => 'قيد الانتظار',
    'status_confirmed' => 'مؤكد',
    'status_cancelled' => 'ملغي',
    'status_completed' => 'مكتمل',

    // Visit type labels
    'visit_type_home_visit' => 'زيارة منزلية',

    // Messages
    'request_submitted_success'  => 'تم تقديم طلب الزيارة بنجاح.',
    'request_cancelled_success'  => 'تم إلغاء طلب الزيارة بنجاح.',
    'request_not_found'          => 'لم يتم العثور على طلب الزيارة.',
    'request_already_cancelled'  => 'تم إلغاء طلب الزيارة هذا بالفعل.',
    'patient_cancel_not_allowed' => 'يمكن للمريض إلغاء الطلب فقط أثناء بقائه قيد الانتظار. يرجى التواصل مع الدعم للزيارة المُسندة.',
    'status_updated_success'     => 'تم تحديث حالة الطلب بنجاح.',
    'doctor_assigned_success'    => 'تم تعيين الطبيب بنجاح.',
    'notes_updated_success'      => 'تم تحديث الملاحظات بنجاح.',
    'invalid_status_transition'  => 'هذا التغيير في الحالة غير مسموح به.',
    'unauthorized_status_update' => 'ليس لديك صلاحية لتحديث هذا الطلب.',
];
