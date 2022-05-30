<?php

/**
 * Aqui você tem quem preencher com o Cookie do seu navegador.
 *
 * @var string Cookie completo do Pull Approve do seu navegador (com todas autenticações).
 */
const COOKIE = '';

print_r("SCRIPT PARA SINCRONIZAR TODAS PRs ABERTAS NO PULLAPPROVE\n");
syncPrs(getPrs());

/**
 * Monta um array com todas as PRs com status `open`.
 *
 * @return int[] Array com os números das PRs que precisam ser sincronizadas.
 */
function getPrs(): array
{
    $cookie = COOKIE;
    $pagina = 1;
    $ultimaPagina = 0;
    $prs = [];
    do {
        print_r("\n\nIdentificando as PRs da página {$pagina}:\n");
        exec(
            "curl 'https://pullapprove.com/Superlogica/cloud/?page={$pagina}' \
        -H 'Connection: keep-alive' \
        -H 'Pragma: no-cache' \
        -H 'Cache-Control: no-cache' \
        -H 'sec-ch-ua: \" Not A;Brand\";v=\"99\", \"Chromium\";v=\"99\", \"Google Chrome\";v=\"99\"' \
        -H 'sec-ch-ua-mobile: ?0' \
        -H 'sec-ch-ua-platform: \"Linux\"' \
        -H 'Upgrade-Insecure-Requests: 1' \
        -H 'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.84 Safari/537.36' \
        -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9' \
        -H 'Sec-Fetch-Site: none' \
        -H 'Sec-Fetch-Mode: navigate' \
        -H 'Sec-Fetch-User: ?1' \
        -H 'Sec-Fetch-Dest: document' \
        -H 'Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7' \
        -H 'Cookie: {$cookie}' \
        --compressed",
            $output
        );

        foreach ($output as $linha) {
            if (
                $pagina === 1
                && strpos($linha, '/Superlogica/cloud/?page=') !== false
            ) {
                preg_match("/\/Superlogica\/cloud\/\?page=(\d+)/", $linha, $matches);
                if ($matches[1] > $ultimaPagina) {
                    $ultimaPagina = $matches[1];
                }
            }

            if (strpos($linha, '/Superlogica/cloud/pull-request/') !== false) {
                preg_match("/\/Superlogica\/cloud\/pull-request\/(\d+)\//", $linha, $matches);
                $prs[$matches[1]] = $matches[1];
            }
        }

        $pagina++;
    } while ($pagina <= $ultimaPagina);

    sort($prs);
    return array_values($prs);
}

/**
 * Sincroniza todas as PRs informadas.
 *
 * @param  array $prs
 * @return void
 */
function syncPrs(array $prs)
{
    print_r("\n\n\nSINCRONIZANDO AS PRs\n\n\n");
    $cookie = COOKIE;
    print_r($prs);die;
    foreach ($prs as $pr) {
        print_r("PR {$pr}:");
        exec("curl 'https://pullapprove.com/Superlogica/cloud/pull-request/{$pr}/sync/' \
        -H 'Connection: keep-alive' \
        -H 'Pragma: no-cache' \
        -H 'Cache-Control: no-cache' \
        -H 'sec-ch-ua: \" Not A;Brand\";v=\"99\", \"Chromium\";v=\"99\", \"Google Chrome\";v=\"99\"' \
        -H 'sec-ch-ua-mobile: ?0' \
        -H 'sec-ch-ua-platform: \"Linux\"' \
        -H 'Upgrade-Insecure-Requests: 1' \
        -H 'Origin: https://pullapprove.com' \
        -H 'Content-Type: application/x-www-form-urlencoded' \
        -H 'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.84 Safari/537.36' \
        -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9' \
        -H 'Sec-Fetch-Site: same-origin' \
        -H 'Sec-Fetch-Mode: navigate' \
        -H 'Sec-Fetch-User: ?1' \
        -H 'Sec-Fetch-Dest: document' \
        -H 'Referer: https://pullapprove.com/Superlogica/cloud/pull-request/{$pr}/' \
        -H 'Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7' \
        -H 'Cookie: {$cookie}' \
        --data-raw 'csrfmiddlewaretoken=8hBI4rUK9Q8HvwnHgvCVxEH2hO3X2PQ7' \
        --compressed");
        print_r("\n\n");
    }
}
