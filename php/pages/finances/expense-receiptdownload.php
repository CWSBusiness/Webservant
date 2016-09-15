<?php

try {
	$expense = FinanceRecord::withID($_GET["expense"]);
} catch (OutOfBoundsException $e) {
	Auth::redirect("./?finances");
}

if (!$expense->getExpenseReceipt()) {
	Auth::redirect("./?finances&expense=" . $expense->getPID());
}

// File type output
header("Content-type: application/pdf");
// Set as attachment and name the file
header('Content-Disposition: attachment; filename="Receipt - ' . $expense->getDescription() . '"');
// Get the source file
readfile($expense->getExpenseReceipt());