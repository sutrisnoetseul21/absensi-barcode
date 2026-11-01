module.exports = {
  apps: [
    {
      name: "absensi-queue-notifications",
      script: "artisan",
      interpreter: "php",
      args: "queue:work --queue=notifications --sleep=1 --tries=3 --timeout=60",
      instances: 1,
      exec_mode: "fork",
      autorestart: true,
      watch: false,
      max_memory_restart: "500M",
      error_file: "storage/logs/queue-notif-error.log",
      out_file: "storage/logs/queue-notif-out.log"
    },
    {
      name: "absensi-queue-default",
      script: "artisan",
      interpreter: "php",
      args: "queue:work --queue=default --sleep=3 --tries=3 --timeout=150",
      instances: 1,
      exec_mode: "fork",
      autorestart: true,
      watch: false,
      max_memory_restart: "1G",
      error_file: "storage/logs/queue-default-error.log",
      out_file: "storage/logs/queue-default-out.log"
    }
  ]
};
