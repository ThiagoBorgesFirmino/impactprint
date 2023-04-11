
<?php
 /* Adaptado por Leandro Rodrigues 14/10/2019 
	 Arquivo de inserção dos arquivos pelo CSV.*/
	 
require '../global.php';

require 'Encoding.php';



class importar{



	public function salvar(){



		$indice = intval(request('indice'));

		$aux = true;



		$linhas = file('csv'.DIRECTORY_SEPARATOR.'first_test.csv');



		print "".sizeof($linhas)." linhas.<br /><br />";

		$cont = 0;

		if(isset($_REQUEST['cont'])){

			$cont = $_REQUEST['cont'];

		}

		printr($indice);

		if($indice<(sizeof($linhas)+1)){



			if($indice>0){

                $linhas = Encoding::toUTF8($linhas);

                $arr = list($sku_id,$referencia, $nome, $tamanho, $img, $cor, $legenda) = explode(';', $linhas[$indice]);
                
              
 
				//$img2 = $cor;
    
				printr($arr);
				//var_dump($arr[6]); die();

				if($sku_id !=''){


                
                 $cor_new = "SELECT id from cor where nome = '{$cor}' limit 1";
                 $cor_result = mysql_num_rows(query($cor_new));
                 //echo $cor_result; die();
              
                 if($cor_result == 0){
                        $nova = "INSERT INTO cor (nome) value( '{$cor}' ) ";
                        if(query($nova)){
                            $push = "SELECT id from cor where nome = '{$cor}' ";
                            $count = mysql_result(query($push, 0,0));
                        }

                }else{
                        $count = mysql_result(query($cor_new), 0,0);
                     }

                //$count = mysql_result(query($cor_new),0,0);

                    $nome = str_replace('.','',$nome);
                    $sku_id = str_replace('.','-',$referencia);
                    $tagnome = $nome ."-".$referencia;
					$referencia_pai = substr($sku_id, 0, -3);
					$legenda = substr($legenda,0,-13);
				

                    
               $sku_new = "SELECT itemsku_id from item where id = '{$sku_id}'";
			   $sku_test = mysql_num_rows(query($sku_new));
			  //	 echo $sku_test; die();
               if($sku_test == 0){
                   $sku = query("INSERT INTO item (id,referencia, descricao, nome, imagem, tamanho_total, cor_id, tag_nome, tipo_produto, fornecedor_1,fornecedor_2)
                   VALUES	('{$sku_id}','{$referencia_pai}', '{$legenda}','{$nome}','{$img}','{$tamanho}' , '{$count}','{$tagnome}','CR' , '{$sku_id}','SPOT')" );
               }
          
					$sql = "INSERT INTO item (itemsku_id,referencia, descricao, nome, imagem, tamanho_total, cor_id, tag_nome, tipo_produto, fornecedor_1,fornecedor_2)
                            VALUES	('{$sku_id}','{$sku_id}', '{$legenda}','{$nome}','{$img}','{$tamanho}' ,'{$count}','{$tagnome}','CR', '{$sku_id}','SPOT')";
               




                    if(!query($sql)){

                        // printr($item_xbz);

                        printr($sql);

                        // $handle = fopen("PRODUTOS_NAO_ENCONTRADOS_PRIETO_2.txt", "a+");

                        // fwrite($handle,"ERRO_UP:{$sku_id}>>{$custo}".PHP_EOL );

                      //  fclose($handle);



                        //die();

                    }

                }else{

					printr($sku_id);

					//$handle = fopen("PRODUTOS_NAO_ENCONTRADOS_PRIETO_hoje.txt", "a+");

					//fwrite($handle,"{$sku_id}>>{$custo}".PHP_EOL );

				//	fclose($handle);

				}

				

				

				//die();

				print "<br />";



				$aux = true;

			}

			

		}else{

			echo "importacao Finalizada";

			die();

		}



		if(!$aux){

			echo "

					<form method='post' action='#' enctype='multipart/form-data'>

					<input type='hidden' value='salvar' name='action' />

						adicionar CSV: <input type='file' name='importar_file' />

						<br />

						<input type='submit' value='Enviar' />

					</form>



				";

		}else{

			$this->gonext($indice);

		}

	}



	public function converte($str){

		$str = str_replace("{{barra}}","/",$str);

		return addslashes(trim(iconv('iso-8859-1','utf-8//translit', $str)));

	}



	public function converteMoney($str){

		return addslashes(str_replace(",","."," ",trim(iconv('iso-8859-1','utf-8//translit', $str))));

	}



	public function converte1($str){

		return strip_tags(trim(iconv('iso-8859-1','utf-8//translit', $str)));

	}

	

	public function converte2($str){

		return (trim(iconv('iso-8859-1','utf-8//translit', $str)));

	}

	

	public function gonext($indice){

		$indice ++;

		print '<meta http-equiv="refresh" content="0.5; url=upload_gnome.php?indice='.$indice.'">';

	}





	public function dataConverte($data){

		$aux ='';

		$y="";

		$m="";

		$d="";

		$ref= 0;

		for($j=0; $j<strlen($data); $j++){

		  if($data[$j] != "/"){

			  switch ($ref){

				  case 0: $d .= $data[$j]; break;

				  case 1: $m.= $data[$j]; break;

				  case 2: $y .= $data[$j]; break;

				  default: break;

				}

			}else{

				$ref++;

			}

		}

		$y  = trim($y);

		$m = trim($m);

		$d  = trim($d);

		$aux .= $y;

		$aux .='-';

		$aux .=$m;

		$aux .='-';

		$aux .=$d;



		if($aux == '--'){

			$aux = '0000-00-00';

		}

		return $aux;

	}



}

$imp = new importar();

$imp->salvar();

