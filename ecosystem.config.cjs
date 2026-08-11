module.exports = {
  apps: [
    {
      name: "absensi-queue",
      script: "artisan",
      interpreter: "/usr/bin/php8.3",
      args: "queue:work --sleep=3 --tries=3 --timeout=3600",
      instances: 1,
      exec_mode: "fork",
      autorestart: true,
      watch: false,
      max_memory_restart: "1G",
      error_file: "storage/logs/queue-error.log",
      out_file: "storage/logs/queue-out.log"
    }
  ]
};
