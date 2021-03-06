<?php
namespace Eduardokum\LaravelBoleto\Boleto\Banco;

use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Util;

class Santander  extends AbstractBoleto implements BoletoContract
{
    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = self::COD_BANCO_SANTANDER;
    /**
     * Define as carteiras disponíveis para este banco
     *
     * @var array
     */
    protected $carteiras = ['101', '201'];
    /**
     * Espécie do documento, coódigo para remessa
     *
     * @var string
     */
    protected $especiesCodigo = [
        'DM' => '01',
        'NP' => '02',
        'NS' => '03',
        'REC' => '05',
        'DS' => '06',
        'LC' => '07',
    ];
    /**
     * Define os nomes das carteiras para exibição no boleto
     *
     * @var array
     */
    protected $carteirasNomes = ['101' => 'Cobrança Simples ECR', '102' => 'Cobrança Simples CSR'];
    /**
     * Define o valor do IOS - Seguradoras (Se 7% informar 7. Limitado a 9%) - Demais clientes usar 0 (zero)
     *
     * @var int
     */
    protected $ios = 0;
    /**
     * Variaveis adicionais.
     *
     * @var array
     */
    public $variaveis_adicionais = [
        'esconde_uso_banco' => true,
    ];
    /**
     * Define o código da carteira (Com ou sem registro)
     *
     * @param  string $carteira
     * @return AbstractBoleto
     * @throws \Exception
     */
    public function setCarteira($carteira)
    {
        switch ($carteira)
        {
        case '1':
        case '5':
            $carteira = '101';
            break;
        case '4':
            $carteira = '102';
            break;
        }
        return parent::setCarteira($carteira);
    }
    /**
     * Define o valor do IOS
     *
     * @param int $ios
     */
    public function setIos($ios)
    {
        $this->ios = $ios;
    }
    /**
     * Retorna o atual valor do IOS
     *
     * @return int
     */
    public function getIos()
    {
        return $this->ios;
    }

    /**
     * Seta dias para baixa automática
     *
     * @param int $baixaAutomatica
     *
     * @return $this
     * @throws \Exception
     */
    public function setDiasBaixaAutomatica($baixaAutomatica)
    {
        if($this->getDiasProtesto() > 0) {
            throw new \Exception('Você deve usar dias de protesto ou dias de baixa, nunca os 2');
        }
        if(!in_array($baixaAutomatica, [15, 30])) {
            throw new \Exception('O Banco Santander so aceita 15 ou 30 dias após o vencimento para baixa automática');
        }
        $baixaAutomatica = (int) $baixaAutomatica;
        $this->diasBaixaAutomatica = $baixaAutomatica > 0 ? $baixaAutomatica : 0;
        return $this;
    }

    /**
     * Método que valida se o banco tem todos os campos obrigadotorios preenchidos
     */
    public function isValid()
    {
        if($this->numeroDocumento == '' 
            || $this->conta == '' 
            || $this->carteira == ''
        ) {
            return false;
        }
        return true;
    }
    /**
     * Gera o Nosso Número.
     *
     * @return string
     */
    protected function gerarNossoNumero()
    {
        $nossoNumero = Util::numberFormatGeral($this->getNumeroDocumento(), 12);
        $nossoNumero .= Util::modulo11($this->getNumeroDocumento());
        return $nossoNumero;
    }
    /**
     * Método para gerar o código da posição de 20 a 44
     *
     * @return string
     */
    protected function getCampoLivre()
    {
        if ($this->campoLivre) {
            return $this->campoLivre;
        }
        return $this->campoLivre = '9' . Util::numberFormatGeral($this->getConta(), 7) .
        $this->getNossoNumero() .
        Util::numberFormatGeral($this->getIos(), 1) .
        Util::numberFormatGeral($this->getCarteira(), 3);
    }
}