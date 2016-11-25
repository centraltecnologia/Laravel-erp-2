@extends('main')
@section('content')
  <div class="row">
    <div class="col-md-11">
      <form method="POST" action="{{ url('/contatos') }}/{{$contato->id}}/relacoes/novo">
        <div class="form-group">
        {{ csrf_field() }}
        <div class="panel panel-default">
          <div class="panel-heading"><i class="fa fa-users fa-1x"></i> Novo relacionamento de <span class="label label-primary">{{$contato->nome}}</span></div>
          <div class="panel-body">
            <div class="row text-right">
              <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-success">Salvar</button>
              </div>
            </div>
            <div class="row form-inline">
              <div class="col-md-3">
                <div class="form-group">
                  <label for="text">Relacionar <span class="label label-primary">{{$contato->nome}}</span> com:</label>
                  @foreach($contatos as $key => $contato_to)
                    @if($contato_to->id==$contato->id)
                    @else
                      <div class="row list-contacts text-center">
                        <input type="radio" name="to_id" value="{{$contato_to->id}}" onclick="$('#to').text('{{$contato_to->nome}}'); $('#to2').text('{{$contato_to->nome}}'); $('#form').show()" > {{$contato_to->nome}}
                      </div>
                    @endif
                  @endforeach
                </div>
              </div>
              <div class="col-md-9" style="display:none;" id="form">
                <div class="row">
                  <div class="form-group">
                    <label for="text"><span class="label label-primary">{{$contato->nome}}</span> é </label>
                    <input type="text" class="form-control" value="" name="from_text" id="from_text" placeholder="Texto de relacionamento">
                    de <span id="to" class="label label-success"></span>
                  </div>
                </div>
                <div class="row">
                  <div class="form-group">
                    <label for="text"><span id="to2" class="label label-success"></span> é </label>
                    <input type="text" class="form-control" value="" name="to_text" id="to_text" placeholder="Texto de relacionamento">
                    de <span class="label label-primary">{{$contato->nome}}</span>
                  </div>
                </div>
              </div>
            </div>
        </div>
      </form>
    </div>
  </div>
@endsection