@extends('errors.layout')

@section('title', 'Access Forbidden')
@section('code', '403')
@section('message', 'You do not have permission to view this resource. Role-based access control is actively enforced on this endpoint.')
