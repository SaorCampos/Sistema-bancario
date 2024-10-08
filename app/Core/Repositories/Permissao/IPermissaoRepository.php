<?php

namespace App\Core\Repositories\Permissao;

use App\Core\ApplicationModels\Pagination;
use App\Core\ApplicationModels\PaginatedList;
use App\Http\Requests\Permissao\PermissaoListingRequest;
use App\Models\Permissao;
use Illuminate\Support\Collection;

interface IPermissaoRepository
{
    public function getPermissoes(PermissaoListingRequest $request, Pagination $pagination): PaginatedList;
    public function updatePermissao(string $id, Permissao $permissao): bool;
    public function deletePermissao(string $id): bool;
    public function getPermissoesAtivasByIdList(array $ids): Collection;
    public function getAllPermissoesByIdList(array $ids): Collection;
}
