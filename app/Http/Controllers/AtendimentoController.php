<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Atendimento as Atendimento;
use App\Contatos as Contatos;
use App\Attachments as Attachs;
use App\Combobox_texts as Comboboxes;
use Carbon\Carbon;
use Log;
use Auth;
use Intervention\Image\ImageManagerStatic as Image;
use File;
use Storage;

class AtendimentoController  extends BaseController
{
  public function __construct(){
     parent::__construct();
  }

  public function index(){
    Log::info('Mostando atendimentos, para -> ID:'.Auth::user()->contato->id.' nome:'.Auth::user()->contato->nome.' Usuario ID:'.Auth::user()->id.' ip:'.request()->ip());
    if (!isset(Auth::user()->perms["atendimentos"]["leitura"]) or Auth::user()->perms["atendimentos"]["leitura"]!=1){
      return redirect()->action('HomeController@index')
                       ->withErrors([__('messages.perms.leitura')]);
    }
    $atendimentos = Atendimento::orderBy('created_at', 'desc')->paginate(15);
    $deletados = "0";
    $total= Atendimento::count();
    $comboboxes = comboboxes::where('combobox_textable_type', 'App\Atendimentos')->get();
    return view('atend.index')->with('atendimentos', $atendimentos)->with('total', $total)->with('comboboxes', $comboboxes)->with('deletados', $deletados);
  }

  public function show($id){
    if (!isset(Auth::user()->perms["atendimentos"]["edicao"]) or Auth::user()->perms["atendimentos"]["edicao"]!=1){
      return redirect()->action('HomeController@index')
                       ->withErrors([__('messages.perms.edicao')]);
    }
    $atendimento = Atendimento::find($id);
    $comboboxes = comboboxes::where('combobox_textable_type', 'App\Atendimentos')->get();
    Log::info('Mostando atendimento -> "'.$atendimento.'", para -> ID:'.Auth::user()->contato->id.' nome:'.Auth::user()->contato->nome.' Usuario ID:'.Auth::user()->id.' ip:'.request()->ip());

    return view('atend.novo')->with('atendimento', $atendimento)->with('comboboxes', $comboboxes);
  }

  public function detalhes($id){


    $atendimento = Atendimento::find($id);
    Log::info('Mostando detalhes atendimento -> "'.$atendimento.'", para -> ID:'.Auth::user()->contato->id.' nome:'.Auth::user()->contato->nome.' Usuario ID:'.Auth::user()->id.' ip:'.request()->ip());

    return view('atend.detalhes')->with('atendimento', $atendimento);
  }

  public function new_a(){
    if (!isset(Auth::user()->perms["atendimentos"]["adicao"]) or Auth::user()->perms["atendimentos"]["adicao"]!=1){
      return redirect()->action('HomeController@index')
                       ->withErrors([__('messages.perms.adicao')]);
    }
    Log::info('Criando novo atendimento, selecionando contato, para -> ID:'.Auth::user()->contato->id.' nome:'.Auth::user()->contato->nome.' Usuario ID:'.Auth::user()->id.' ip:'.request()->ip());
    $contatos = Contatos::paginate(15);
    $comboboxes = comboboxes::where('combobox_textable_type', 'App\Atendimentos')->get();
    return view('atend.novo')->with('contatos', $contatos)->with('comboboxes', $comboboxes);
  }

  public function add(Request $request){
    if (!isset(Auth::user()->perms["atendimentos"]["adicao"]) or Auth::user()->perms["atendimentos"]["adicao"]!=1){
      return redirect()->action('HomeController@index')
                       ->withErrors([__('messages.perms.adicao')]);
    }
    $this->validate($request, [
        'assunto' => 'required|max:50',
        'contatos_id' => 'required',
    ]);
    $atendimento = new Atendimento;
    $atendimento->assunto = $request->assunto;
    $atendimento->contatos_id = $request->contatos_id;
    $atendimento->created_at = $request->data;
    $atendimento->texto = $request->texto;
    $atendimento->save();

    Log::info('Salvando atendimento -> "'.$atendimento.'", para -> ID:'.Auth::user()->contato->id.' nome:'.Auth::user()->contato->nome.' Usuario ID:'.Auth::user()->id.' ip:'.request()->ip());

    return redirect()->action('AtendimentoController@index');
  }

  public function delete($id){
    if (!isset(Auth::user()->perms["atendimentos"]["edicao"]) or Auth::user()->perms["atendimentos"]["edicao"]!=1){
      return redirect()->action('HomeController@index')
                       ->withErrors([__('messages.perms.edicao')]);
    }
    $atendimento = Atendimento::withTrashed()->find($id);
    Log::info('Deletando atendimento -> "'.$atendimento.'", para -> ID:'.Auth::user()->contato->id.' nome:'.Auth::user()->contato->nome.' Usuario ID:'.Auth::user()->id.' ip:'.request()->ip());
    if ($atendimento->trashed()) {
      $atendimento->restore();
    } else {
      $atendimento->delete();
    }

    return redirect()->action('AtendimentoController@index');
  }

  public function search( Request $request)
  {
    if (!isset(Auth::user()->perms["atendimentos"]["leitura"]) or Auth::user()->perms["atendimentos"]["leitura"]!=1){
      return redirect()->action('HomeController@index')
                       ->withErrors([__('messages.perms.leitura')]);
    }
    $atendimentos = atendimento::query();
    if ($request->busca!=""){
      $contatos = Contatos::where('nome', 'like', '%' .  $request->busca . '%')
                            ->orWhere('sobrenome', 'like', '%' .  $request->busca . '%')
                            ->orWhere('endereco', 'like', '%' .  $request->busca . '%')
                            ->orWhere('cpf', 'like', '%' .  $request->busca . '%')
                            ->orWhere('cidade', 'like', '%' .  $request->busca . '%')
                            ->orWhere('uf', 'like', '%' .  $request->busca . '%')
                            ->orWhere('bairro', 'like', '%' .  $request->busca . '%')
                            ->orWhere('cep', 'like', '%' .  $request->busca . '%')
                            ->get();
        $a = 0;
        while ($a < count($contatos)) {
          $atendimentos = $atendimentos->where('contatos_id', '=', $contatos[$a]->id);
          $a++;
        }
    }
    if ($request->assunto!=""){
      $atendimentos = $atendimentos->Where('assunto', $request->assunto);
    }
    if ($request->data_de and !$request->data_ate){
      $atendimentos = $atendimentos->whereBetween('created_at', [$request->data_de, Carbon::today()]);
    }
    if ($request->data_de and $request->data_ate){
      $atendimentos = $atendimentos->whereBetween('created_at', [$request->data_de, $request->data_ate]);
    }
    $atendimentos = $atendimentos->orderBy('created_at', 'desc')->paginate(30);
    if ((is_array(Auth::user()->perms) and Auth::user()->perms["admin"]==1) and $request->deletados){
        $deletados = atendimento::onlyTrashed()->get();
    } else {
      $deletados = 0;
    }

    $total= Atendimento::count();
    $comboboxes = comboboxes::where('combobox_textable_type', 'App\Atendimentos')->get();

    Log::info('Mostando atendimentos com busca -> "'.$request->busca.'", para -> ID:'.Auth::user()->contato->id.' nome:'.Auth::user()->contato->nome.' Usuario ID:'.Auth::user()->id.' ip:'.request()->ip());

    return view('atend.lista')->with('atendimentos', $atendimentos)->with('total', $total)->with('comboboxes', $comboboxes)->with('deletados', $deletados);
  }

/*
  public function novo($id){
    $contato = Contatos::find($id);

    Log::info('Criando novo atendimento para contato -> "'.$contato.'", para -> ID:'.Auth::user()->contato->id.' nome:'.Auth::user()->contato->nome.' Usuario ID:'.Auth::user()->id.' ip:'.request()->ip());
    return view('atend.index')->with('contato', $contato);
  }
*/
  public function edit(request $request, $id){
    if (!isset(Auth::user()->perms["atendimentos"]["edicao"]) or Auth::user()->perms["atendimentos"]["edicao"]!=1){
      return redirect()->action('HomeController@index')
                       ->withErrors([__('messages.perms.edicao')]);
    }
    $this->validate($request, [
        'assunto' => 'required|max:50',
    ]);
    $atendimento = Atendimento::find($id);

    $atendimento->assunto = $request->assunto;
    $atendimento->texto = $request->texto;
    $atendimento->save();
    Log::info('Editando atendimento com -> "'.$atendimento.'", para -> ID:'.Auth::user()->contato->id.' nome:'.Auth::user()->contato->nome.' Usuario ID:'.Auth::user()->id.' ip:'.request()->ip());

    return redirect()->action('AtendimentoController@index');
  }
  public function attach(request $request, $id){

    $atendimento = Atendimento::find($id);
    $attach = new Attachs;
    $attach->attachmentable_id = $id;
    $attach->attachmentable_type = "App\Atendimento";
    $attach->name = $request->name;
    $attach->path = $request->file->store('public');
    $attach->contatos_id = $atendimento->contatos_id;
    $attach->save();

    $path = storage_path() . '/' .'app/'. $attach->path;
    $extension = File::extension($attach->path);
    if ($extension=="JPG" or $extension=="JPEG" or $extension=="PNG" or $extension=="GIF") {
      $file = Image::make($path);
    } else {
      $file = Storage::put(storage_path() . '/' .'app', $request->file);
    }

    Log::info('Anexando arquivo para atendimento, anexo -> "'.$attach.'", para -> ID:'.Auth::user()->contato->id.' nome:'.Auth::user()->contato->nome.' Usuario ID:'.Auth::user()->id.' ip:'.request()->ip());

    return redirect()->action('AtendimentoController@index');
  }

}
