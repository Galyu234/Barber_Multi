<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Queue;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class QueueService
{
    /**
     * Join antrian cabang berdasarkan branch_code dan session token pelanggan.
     */
    public function join(string $branchCode, string $sessionToken): array
    {
        $branch = Branch::where('code', $branchCode)->where('is_active', true)->first();

        if (!$branch) {
            return ['success' => false, 'message' => 'Cabang tidak ditemukan atau tidak aktif.'];
        }

        // Cek apakah session ini sudah ada di antrian aktif cabang ini
        $existing = Queue::where('customer_session', $sessionToken)
            ->where('branch_id', $branch->id)
            ->whereIn('status', ['waiting', 'in_progress', 'serving'])
            ->first();

        if ($existing) {
            return [
                'success'        => false,
                'message'        => 'Anda sudah berada dalam antrian ini.',
                'queue_number'   => $existing->formatted_queue_number,
                'position'       => $existing->position,
                'estimated_wait' => $existing->estimated_wait,
                'token'          => $existing->customer_session,
            ];
        }

        return DB::transaction(function () use ($branch, $sessionToken) {
            // Cari nomor antrian tertinggi hari ini
            $maxToday = Queue::where('branch_id', $branch->id)
                ->whereDate('joined_at', now()->toDateString())
                ->lockForUpdate()
                ->max('queue_number') ?? 0;

            // Cari nomor antrian tertinggi yang masih aktif (waiting/in_progress/serving)
            // Ini mencegah duplikasi jika antrian hari sebelumnya belum diselesaikan
            $maxActive = Queue::where('branch_id', $branch->id)
                ->whereIn('status', ['waiting', 'in_progress', 'serving'])
                ->lockForUpdate()
                ->max('queue_number') ?? 0;

            $lastNumber = max($maxToday, $maxActive);
            $queueNumber = $lastNumber + 1;

            // Generate QR unik untuk tiket pelanggan
            $queueQr = Str::random(40);

            $queue = Queue::create([
                'branch_id'        => $branch->id,
                'queue_number'     => $queueNumber,
                'customer_session' => $sessionToken,
                'queue_qr'         => $queueQr,
                'status'           => 'waiting',
                'joined_at'        => now(),
            ]);

            $waitingAhead = Queue::where('branch_id', $branch->id)
                ->where('status', 'waiting')
                ->where('joined_at', '<', $queue->joined_at)
                ->count();

            $estimatedWait = $waitingAhead * $branch->avg_service_minutes;

            return [
                'success'        => true,
                'message'        => 'Berhasil masuk antrian!',
                'queue_number'   => $queue->formatted_queue_number,
                'position'       => $waitingAhead + 1,
                'estimated_wait' => $estimatedWait,
                'token'          => $sessionToken,
                'queue_qr'       => $queueQr,
                'branch_name'    => $branch->name,
                'branch_code'    => $branch->code,
            ];
        });
    }

    /**
     * Keluar dari antrian cabang.
     */
    public function leave(string $branchCode, string $sessionToken): array
    {
        $branch = Branch::where('code', $branchCode)->first();

        if (!$branch) {
            return ['success' => false, 'message' => 'Cabang tidak ditemukan.'];
        }

        $queue = Queue::where('customer_session', $sessionToken)
            ->where('branch_id', $branch->id)
            ->whereIn('status', ['waiting', 'in_progress', 'serving'])
            ->first();

        if (!$queue) {
            return ['success' => false, 'message' => 'Anda tidak sedang berada dalam antrian ini.'];
        }

        $queue->update([
            'status'      => 'done',
            'finished_at' => now(),
        ]);

        return [
            'success'      => true,
            'message'      => 'Anda telah keluar dari antrian.',
            'queue_number' => $queue->formatted_queue_number,
        ];
    }

    /**
     * Auto timeout antrian yang melebihi batas waktu.
     */
    public function autoTimeout(): int
    {
        $count = 0;

        Branch::where('is_active', true)->each(function (Branch $branch) use (&$count) {
            $timeout  = $branch->queue_timeout_minutes;
            $deadline = now()->subMinutes($timeout);

            $timedOut = Queue::where('branch_id', $branch->id)
                ->where('status', 'waiting')
                ->where('joined_at', '<', $deadline)
                ->get();

            foreach ($timedOut as $q) {
                $q->update(['status' => 'timeout', 'finished_at' => now()]);
                $count++;
            }

            // Bersihkan antrian 'in_progress'/'serving' yang nyangkut > 3 jam
            $stuckDeadline = now()->subHours(3);
            $stuckServing  = Queue::where('branch_id', $branch->id)
                ->whereIn('status', ['in_progress', 'serving'])
                ->where('joined_at', '<', $stuckDeadline)
                ->get();

            foreach ($stuckServing as $q) {
                $q->update(['status' => 'completed', 'finished_at' => now(), 'notes' => 'Auto-completed by system']);
                $count++;
            }
        });

        return $count;
    }

    /**
     * Ambil status antrian berdasarkan queue_qr token (atau customer_session fallback).
     */
    public function getStatus(string $token, string $branchCode): ?array
    {
        $branch = Branch::where('code', $branchCode)->first();
        if (!$branch) return null;

        $queue = Queue::where('branch_id', $branch->id)
            ->where(function ($q) use ($token) {
                $q->where('queue_qr', $token)
                  ->orWhere('customer_session', $token);
            })
            ->latest('joined_at')
            ->first();

        if (!$queue) return null;

        return [
            'queue_number'   => $queue->formatted_queue_number,
            'status'         => $queue->status,
            'status_label'   => $queue->status_label,
            'position'       => $queue->position,
            'estimated_wait' => $queue->estimated_wait,
            'joined_at'      => $queue->joined_at->format('H:i'),
            'branch_name'    => $branch->name,
        ];
    }
}
