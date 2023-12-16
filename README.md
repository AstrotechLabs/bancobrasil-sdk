# Banco do Brasil SDK para PHP

Este é um repositório que possui uma abstração a API do Banco do Brasil, facilitando a criação de PIX Copia e Cola como também outros serviços oferecidos

## Installation

A forma mais recomendada de instalar este pacote é através do [composer](http://getcomposer.org/download/).

Para instalar, basta executar o comando abaixo

```bash
$ php composer.phar require vaironaegos/bancobrasil-sdk
```

ou adicionar esse linha

```
"vaironaegos/bancobrasil-sdk": "^1.0"
```

na seção `require` do seu arquivo `composer.json`.

## Como Usar?

### Mínimo para usar

```php
$bbService = new BancoBrasilPix(new BancoBrasilPixParams(
    clientId: 'xxxxx',
    clientSecret: 'yyyyy',
    devAppId: 'ccccc',
    // isSandBox: $bbParams['isSandBox'] (opcional)
));

$pixChargeResponse = $bbService->createCharge(new PixData(
    senderName: $contract->customer->user->name,
    senderCpf: $contract->customer->cpf,
    amount: (float)$contract->amount,
    destinationKey: $company->bancoBrasilApi['pixKey'],
    // description: 'Compra XPTO' (Opcional)
));

print_r($pixChargeResponse);
```

Saída

```
@todo
```

## Contributing

Pull Request são bem-vindao. Para mudanças importantes, abra primeiro uma issue para discutir o que você gostaria de mudar.

Certifique-se de atualizar os testes conforme apropriado.

## Licence

Este pacote é lançado sob a licença [MIT](https://choosealicense.com/licenses/mit/). Consulte o pacote [LICENSE](./LICENSE) para obter detalhes.
