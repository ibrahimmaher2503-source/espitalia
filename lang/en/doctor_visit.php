<?php

return [
    // Page titles
    'singular_title' => 'Doctor Visit Request',
    'plural_title'   => 'Doctor Visit Requests',

    // Field labels
    'lbl_reference_number'  => 'Reference Number',
    'lbl_patient_name'      => 'Patient Name',
    'lbl_preferred_doctor'  => 'Preferred Doctor',
    'lbl_assigned_doctor'   => 'Assigned Doctor',
    'lbl_visit_type'        => 'Visit Type',
    'lbl_visit_reason'      => 'Visit Reason',
    'lbl_preferred_date'    => 'Preferred Date',
    'lbl_contact_phone'     => 'Contact Phone',
    'lbl_additional_notes'  => 'Additional Notes',
    'lbl_status'            => 'Status',
    'lbl_admin_notes'       => 'Admin Notes',
    'lbl_cancellation_reason' => 'Cancellation Reason',
    'lbl_completed_at'      => 'Completed At',
    'lbl_action'            => 'Action',

    // Status labels
    'status_pending'    => 'Pending',
    'status_confirmed'  => 'Confirmed',
    'status_cancelled'  => 'Cancelled',
    'status_completed'  => 'Completed',

    // Visit type labels
    'visit_type_home_visit' => 'Home Visit',

    // UI labels
    'lbl_status_history'    => 'Status History',
    'lbl_no_history'        => 'No history yet.',
    'lbl_save_notes'        => 'Save Notes',
    'lbl_update_status'     => 'Update Status',
    'lbl_assign_doctor'     => 'Assign Doctor',
    'lbl_select_doctor'     => '— Select Doctor —',

    // Messages
    'request_submitted_success'  => 'Your visit request has been submitted successfully.',
    'request_cancelled_success'  => 'Your visit request has been cancelled successfully.',
    'request_not_found'          => 'Visit request not found.',
    'request_already_cancelled'  => 'This visit request is already cancelled.',
    'patient_cancel_not_allowed' => 'A patient can cancel this request only while it is pending. Contact support for an assigned visit.',
    'status_updated_success'     => 'Request status updated successfully.',
    'doctor_assigned_success'    => 'Doctor assigned successfully.',
    'notes_updated_success'      => 'Notes updated successfully.',
    'invalid_status_transition'  => 'This status transition is not allowed.',
    'unauthorized_status_update' => 'You are not authorized to update this request.',
];
