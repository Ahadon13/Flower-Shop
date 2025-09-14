<?php
// Toast Alert Component (SweetAlert2)
function renderAlerts($messages = []) {
    if (empty($messages)) return;

    echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
    echo '<script>';
    foreach ($messages as $msg) {
        $type = stripos($msg, 'success') !== false ? 'success' : 'error';

        echo "Swal.fire({
            toast: true,
            position: 'top-end',
            icon: '{$type}',
            title: '" . addslashes($msg) . "',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            customClass: {
                popup: 'small-toast'
            }
        });";
    }
    echo '</script>';
}
?>