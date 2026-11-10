@props(['role' => 'staf'])
@php($label = ['admin' => 'Admin', 'resepsionis' => 'Resepsionis', 'groomer' => 'Groomer'][$role] ?? ucfirst($role))
<span {{ $attributes->class(['role-badge', 'role-badge--'.$role]) }}>{{ $label }}</span>
