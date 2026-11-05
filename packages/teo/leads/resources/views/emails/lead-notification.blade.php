<!DOCTYPE html>
<html lang="ru">
<head><meta charset="utf-8"><title>Новая заявка</title></head>
<body style="font-family: Arial, sans-serif; background:#f8fafc; padding:24px; color:#0f172a;">
  <div style="max-width:560px; margin:0 auto; background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:24px;">
    <h2 style="margin:0 0 16px; font-size:20px;">Новая заявка с сайта</h2>
    <table style="width:100%; border-collapse:collapse; font-size:15px;">
      <tr><td style="padding:8px 0; color:#64748b; width:120px;">Имя:</td><td style="padding:8px 0;">{{ $lead->name }}</td></tr>
      <tr><td style="padding:8px 0; color:#64748b;">Телефон:</td><td style="padding:8px 0;">{{ $lead->phone }}</td></tr>
      @if($lead->email)
      <tr><td style="padding:8px 0; color:#64748b;">E-mail:</td><td style="padding:8px 0;">{{ $lead->email }}</td></tr>
      @endif
      @if($lead->message)
      <tr><td style="padding:8px 0; color:#64748b;">Сообщение:</td><td style="padding:8px 0;">{{ $lead->message }}</td></tr>
      @endif
      <tr><td style="padding:8px 0; color:#64748b;">Дата:</td><td style="padding:8px 0;">{{ $lead->created_at->format('d.m.Y H:i') }}</td></tr>
    </table>
    <p style="margin:20px 0 0;"><a href="{{ rtrim(config('app.url'), '/') }}/admin/leads" style="display:inline-block; padding:10px 18px; background:#2563eb; color:#fff; border-radius:8px; text-decoration:none;">Открыть в админке</a></p>
  </div>
</body>
</html>
