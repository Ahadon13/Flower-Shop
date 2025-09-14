<?php
function renderPagination($totalRecords, $recordsPerPage = 6, $currentPage = 1, $url = '?page=') {
    $totalPages = ceil($totalRecords / $recordsPerPage);
    if ($totalPages <= 1) return;

    echo '<div class="pagination">';
    
    // Prev button
    if ($currentPage > 1) {
        echo '<a href="' . $url . ($currentPage - 1) . '">&laquo; Prev</a>';
    }

    // Page numbers
    for ($i = 1; $i <= $totalPages; $i++) {
        $active = $i == $currentPage ? "active" : "";
        echo '<a class="' . $active . '" href="' . $url . $i . '">' . $i . '</a>';
    }

    // Next button
    if ($currentPage < $totalPages) {
        echo '<a href="' . $url . ($currentPage + 1) . '">Next &raquo;</a>';
    }

    echo '</div>';
}
?>

<style>
.pagination {
    display: flex;
    justify-content: center;
    margin: 20px 0;
    gap: 6px;
}

.pagination a {
    padding: 8px 14px;
    border: 1px solid #ccc;
    border-radius: 6px;
    background: #fff;
    color: #2c3e50;
    text-decoration: none;
    transition: all 0.2s ease;
}

.pagination a:hover {
    background: #3498db;
    color: white;
    border-color: #2980b9;
}

.pagination a.active {
    background: #3498db;
    color: white;
    font-weight: bold;
    border-color: #2980b9;
}
</style>