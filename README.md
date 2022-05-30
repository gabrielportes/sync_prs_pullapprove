# Script para sincronizar as PRs em aberto do Pull Approve

Script útil para sincronizar as PRs abertas, quando via interface não funciona devido a quantidade de PRs abertas.

Única coisa que precisa fazer para executar o script, é colocar o cookie do seu navegador autenticado no Pull Approve https://pullapprove.com/Superlogica/cloud/ na const `COOKIE`.

Como obter o cookie pelo Network do navegador:
![image](https://user-images.githubusercontent.com/35439823/171057119-46bb57a0-d105-4a6e-9724-98292f7f923f.png)

### Como executar o script
```sh
php sync_prs.php
```
Troque o valor da const `REPO` pelo repositório que deseja sincronizar.
