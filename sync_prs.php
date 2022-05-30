<?php

/**
 * Aqui você tem quem preencher com o Cookie do seu navegador.
 *
 * @var string Cookie completo do Pull Approve do seu navegador (com todas autenticações).
 */
const COOKIE = '';

/**
 * Esse token tem no Cookie e tem também no playload quando você sincroniza a PR via interface.
 *
 * @var string Token CSRF.
 */
const CSRF = 'csrfmiddlewaretoken=';

/**
 * Repositório que você deseja sincronizar.
 *
 * @var string Repositório.
 */
const REPO = '';

/**
 * Url do Pull Approve com o repositório.
 *
 * @var string Url do Pull Approve.
 */
const PULLAPPROVE_REPO = 'https://pullapprove.com/' . REPO;

print_r("SCRIPT PARA SINCRONIZAR TODAS PRs ABERTAS NO PULLAPPROVE\n");
syncPrs(getPrs());

/**
 * Monta um array com todas as PRs com status `open`.
 *
 * @return int[] Array com os números das PRs que precisam ser sincronizadas.
 */
function getPrs(): array
{
    $repo = REPO;
    $repoParaRegex = str_replace('/', '\/', $repo);
    $pullApproveRepo = PULLAPPROVE_REPO;
    $cookie = COOKIE;
    $pagina = 1;
    $ultimaPagina = 0;
    $prs = [];
    do {
        print_r("\n\nIdentificando as PRs da página {$pagina}:\n");
        exec(
            "curl '{$pullApproveRepo}/?page={$pagina}' \
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
                && strpos($linha, "/{$repo}/?page=") !== false
            ) {
                preg_match("/\/{$repoParaRegex}\/\?page=(\d+)/", $linha, $matches);
                if ($matches[1] > $ultimaPagina) {
                    $ultimaPagina = $matches[1];
                }
            }

            if (strpos($linha, "/{$repo}/pull-request/") !== false) {
                preg_match("/\/{$repoParaRegex}\/pull-request\/(\d+)\//", $linha, $matches);
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
    $pullApproveRepo = PULLAPPROVE_REPO;
    $cookie = COOKIE;
    $dataRaw = CSRF;
    foreach ($prs as $pr) {
        print_r("PR {$pr}:");
        exec("curl '{$pullApproveRepo}/pull-request/{$pr}/sync/' \
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
        -H 'Referer: {$pullApproveRepo}/pull-request/{$pr}/' \
        -H 'Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7' \
        -H 'Cookie: {$cookie}' \
        --data-raw '{$dataRaw}' \
        --compressed");
        print_r("\n\n");
    }
}
