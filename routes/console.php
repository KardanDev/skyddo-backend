<?php

use Illuminate\Support\Facades\Schedule;

// Run daily at 8 AM
Schedule::command('policies:send-renewal-reminders')->dailyAt('08:00');
Schedule::command('policies:mark-expired')->dailyAt('00:00');
Schedule::command('invoices:mark-overdue --send-reminders')->dailyAt('09:00');
