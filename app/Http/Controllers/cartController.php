<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\productModel;
use App\Models\cartModel;
use App\Models\savedCartsModel;
use App\Http\Controllers\DB;
use DateTime;

class cartController extends Controller
{
    //Adiciona o produto escolhido na tabela Cart do banco.
    public function addToCart(Request $request){
        
        $product = $request->produto; // ID do produto selecionado.
        $itemInfo = productModel::where('id', '=', $product)->select('name', 'price')->get();// pega o nome  e preço do produto selecionado por id
        cartModel::insertTest($itemInfo, $product);  //Passa o nome e preço para a função estática no Model , para ser feita a inserção.
        $showCartPrev = cartModel::get('*'); // Pega todos os produtos do cart para preview.
        //queries para calculo do total.
          $tot = cartModel::select('price')->get('*');
          $total = $tot->sum('price'); // faz a soma da collection $subtot contendo o price , e armazena na variavel $subtotal.
            
           if($showCartPrev !=null){
               return view('caixa.caixa', compact('showCartPrev', 'total'));
           }
           else{
              echo "Não foi possível inserir o produto , tente novamente!";
           }
    }
    
    //Remove produto do cart
   public function removeProd(Request $request){
        
    $product = $request->prod; // ID do produto selecionado.
     cartModel::dropItem($product); // Remove o produto do carrinho.
     //queries para calculo do total.
     $tot = cartModel::select('price')->get('*');
     $total = $tot->sum('price'); // faz a soma da collection $subtot contendo o price , e armazena na variavel $subtotal.
     $showCartPrev = cartModel::get('*'); // Pega todos os produtos do cart para preview.
    return view('caixa.caixa', compact('showCartPrev', 'total'));
  }

 // Adiciona os dados do pedido atual na tabela de pedidos , para depois ser feito o fechamento :)
    public function checkOut(){
       $itemInfo = cartModel::select('name', 'price')->get('*');// pega o nome e preço de todos produtos da tabela cart.
        //queries para calculo do total.
    $total = $itemInfo->sum('price');
    return view('caixa.checkOut', compact('itemInfo', 'total')); 
    }

    public function posCheckout(Request $request){
        //Faz o calculo do troco se for no dinheiro , e envia a pagina final de checkout com o  valor do troco.
        $itemInfo = cartModel::select('name', 'price')->get('*');
        $tot = cartModel::select('price')->get('*');
        $total = $tot->sum('price');
        $totalDinheiro = $tot->sum('price'); // Aparece quando o metodo de pagamento é DINHEIRO.
        $vlrRecebido = $request->Dinheiro;
        $date = now(); // data atual para inserir no banco
        // Insert do carrinho na tabela de carrinhos.
        if($total != 0){
            savedCartsModel::saveCart($total, $date); // Salva o carrinho.
        }
        
        if($vlrRecebido != null){ 
            $troco = $request->Dinheiro-$total;
            return view('caixa.posCheckOut', compact('troco', 'itemInfo', 'vlrRecebido', 'totalDinheiro'));
        }
        else{
            return view('caixa.posCheckOut', compact('total', 'itemInfo'));
        }
    }
    //Esvazia o Carrinho de compras , para novo uso.
    public function dropCart(){
        cartModel::clearCart();
        return view('welcome');
    }
}