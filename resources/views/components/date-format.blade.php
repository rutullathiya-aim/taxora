@props(['date'])

{{ $date->isToday() 
    ? 'Today ' . $date->format('H:i') 
    : ($date->isYesterday() 
        ? 'Yesterday ' . $date->format('H:i') 
        : $date->format('d M Y H:i')) 
}}
