@extends('layouts.app')

@section('content')

<div class="container">
  <div class="col-lg-6">
    <history-component :history="{{ $history }}"></history-component>
  </div>
  <div class="col-lg-6">
      <board-component></board-component>
  </div>
</div>

@endsection