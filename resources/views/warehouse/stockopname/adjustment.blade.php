@extends('layouts.adminlte.master')

@section('title')
    @lang('warehouse.stockopname.adjust.title')
@endsection

@section('page_title')
    <span class="fa fa-wrench fa-fw"></span>&nbsp;@lang('warehouse.stockopname.adjust.page_title')
@endsection

@section('page_title_desc')
    @lang('warehouse.stockopname.adjust.page_title_desc')
@endsection

@section('content')
    @if (count($errors) > 0)
        <div class="alert alert-danger">
            <strong>@lang('labels.GENERAL_ERROR_TITLE')</strong> @lang('labels.GENERAL_ERROR_DESC')<br><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="box box-info">
        <div class="box-header with-border">
            <h3 class="box-title">@lang('warehouse.stockopname.adjust.header.title')</h3>
        </div>
        {!! Form::model($stock, ['method' => 'POST', 'route' => ['db.warehouse.stockopname.adjust', $stock->hId()], 'class' => 'form-horizontal', 'data-parsley-validate' => 'parsley']) !!}
            {{ csrf_field() }}
            <div ng-app="stockOpnameModule" ng-controller="stockOpnameController">
                <div class="box-body">
                    <div class="form-group">
                        <label for="inputWarehouse" class="col-sm-2 control-label">@lang('warehouse.stockopname.adjust.field.warehouse')</label>
                        <div class="col-sm-8">
                            <input id="inputWarehouse" type="text" value="{{ $stock->warehouse->name }}" disabled class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputProduct" class="col-sm-2 control-label">@lang('warehouse.stockopname.adjust.field.product')</label>
                        <div class="col-sm-8">
                            <input id="inputProduct" type="text" value="{{ $stock->product->name }}" disabled class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputSupplier" class="col-sm-2 control-label">@lang('warehouse.stockopname.adjust.field.supplier')</label>
                        <div class="col-sm-8">
                            <input id="inputSupplier" type="text" value="{{ empty($stock->purchaseOrder->supplier) ? $stock->purchaseOrder->walk_in_supplier : $stock->purchaseOrder->supplier->name }}" disabled class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputCurrentQuantity" class="col-sm-2 control-label">@lang('warehouse.stockopname.adjust.field.current_quantity')</label>
                        <div class="col-sm-8">
                            <input id="inputCurrentQuantity" type="text" value="{{ $stock->current_quantity }}" disabled class="form-control">
                        </div>
                    </div>
                    <div class="form-group {{ $errors->has('adjusted_quantity') ? 'has-error' : '' }}">
                        <label for="inputAdjustedQuantity"
                               class="col-sm-2 control-label">@lang('warehouse.stockopname.adjust.field.adjusted_quantity')</label>
                        <div class="col-sm-8">
                            <input id="inputAdjustedQuantity" name="adjusted_quantity" type="text" class="form-control"
                                   placeholder="@lang('warehouse.stockopname.adjust.field.adjusted_quantity')"
                                   data-parsley-required="true" data-parsley-pattern="/^\d+(,\d+)*$/" ng-model="adjustedQuantity"
                                   fcsa-number>
                            <span class="help-block">{{ $errors->has('adjusted_quantity') ? $errors->first('adjusted_quantity') : '' }}</span>
                        </div>
                    </div>
                    <div class="form-group {{ $errors->has('reason') ? 'has-error' : '' }}">
                        <label for="inputReason" class="col-sm-2 control-label">@lang('warehouse.stockopname.adjust.field.reason')</label>
                        <div class="col-sm-8">
                            <input id="inputReason" name="reason" type="text" class="form-control" placeholder="@lang('warehouse.stockopname.adjust.field.reason')"
                                   data-parsley-required="true">
                            <span class="help-block">{{ $errors->has('reason') ? $errors->first('reason') : '' }}</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputButton" class="col-sm-2 control-label"></label>
                        <div class="col-sm-8">
                            <a href="{{ route('db.warehouse.stockopname.index') }}" class="btn btn-default">@lang('buttons.cancel_button')</a>
                            <button class="btn btn-default" type="submit">@lang('buttons.submit_button')</button>
                        </div>
                    </div>
                </div>
                <div class="box-footer"></div>
            </div>
        {!! Form::close() !!}
    </div>
@endsection

@section('custom_js')
    <script type="application/javascript">
        var app = angular.module('stockOpnameModule', ['fcsa-number']);
        app.controller("stockOpnameController", ['$scope', function ($scope) {
        }]);
    </script>
@endsection