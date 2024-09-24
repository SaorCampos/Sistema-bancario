<?php

namespace Tests\Feature\Permissao;

use Tests\TestCase;
use App\Models\User;
use App\Models\Perfil;
use App\Models\Permissao;
use Illuminate\Support\Str;
use App\Models\PerfilPermissao;
use Illuminate\Support\Facades\DB;
use Database\Seeders\PermissaoSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PermissaoAtivarTest extends TestCase
{
    use DatabaseTransactions;

    public function test_ativarPermissao_without_beingAuthenticated_returnsUnauthorized(): void
    {
        // Arrange
        $data = [
            'permissoesId' => [(string)Str::uuid()]
        ];
        // Act
        $response = $this->putJson('/api/permissao/ativar', $data);
        $responseBody = json_decode($response->getContent(), true);
        // Assert
        $response->assertUnauthorized();
    }
    public function test_ativarPermissao_with_beingAuthenticated_butWithoutPermission_returnsForbidden(): void
    {
        // Arrange
        User::truncate();
        $user = User::factory()->createOne();
        $this->actingAs($user, 'jwt');
        $data = [
            'permissoesId' => [(string)Str::uuid()]
        ];
        // Act
        $response = $this->putJson('/api/permissao/ativar', $data);
        $responseBody = json_decode($response->getContent(), true);
        // Assert
        $response->assertForbidden();
    }
    public function test_ativarPermissao_with_duplicate_permissions_returnsBadRequest(): void
    {
        // Arrange
        User::truncate();
        Perfil::truncate();
        PerfilPermissao::truncate();
        Permissao::truncate();
        $user = User::factory()->createOne();
        $perfil = Perfil::where('id', '=', (string)$user->perfil_id)->first();
        $this->seed(PermissaoSeeder::class);
        $permissaoIds = DB::table('permissao')->pluck('id');
        $perfilUsuarioId = DB::table('perfil')->where('nome', '=', (string)$perfil->nome)->first()->id;
        $perfilPermissoes = $permissaoIds->map(function ($permissaoId) use ($perfilUsuarioId) {
            return [
                'perfil_id' => $perfilUsuarioId,
                'permissao_id' => $permissaoId,
                'criado_por' => 'Admin',
                'criado_em' => now(),
                'atualizado_por' => 'Admin',
                'atualizado_em' => now(),
            ];
        });
        DB::table('perfil_permissao')->insert($perfilPermissoes->toArray());
        $this->actingAs($user, 'jwt');
        $permissaoId = (string)Str::uuid();
        $data = [
            'permissoesId' => [$permissaoId, $permissaoId]
        ];
        // Act
        $response = $this->putJson('/api/permissao/ativar', $data);
        $responseBody = json_decode($response->getContent(), true);
        // Assert
        $response->assertStatus(400);
        $this->assertEquals('Permissões duplicadas não são permitidas.', $responseBody['message']);
    }
    public function test_ativarPermissao_with_inexisting_permissoes_returnsNotFound(): void
    {
        // Arrange
        User::truncate();
        Perfil::truncate();
        PerfilPermissao::truncate();
        Permissao::truncate();
        $user = User::factory()->createOne();
        $perfil = Perfil::where('id', '=', (string)$user->perfil_id)->first();
        $this->seed(PermissaoSeeder::class);
        $permissaoIds = DB::table('permissao')->pluck('id');
        $perfilUsuarioId = DB::table('perfil')->where('nome', '=', (string)$perfil->nome)->first()->id;
        $perfilPermissoes = $permissaoIds->map(function ($permissaoId) use ($perfilUsuarioId) {
            return [
                'perfil_id' => $perfilUsuarioId,
                'permissao_id' => $permissaoId,
                'criado_por' => 'Admin',
                'criado_em' => now(),
                'atualizado_por' => 'Admin',
                'atualizado_em' => now(),
            ];
        });
        DB::table('perfil_permissao')->insert($perfilPermissoes->toArray());
        $this->actingAs($user, 'jwt');
        $permissaoId = (string)Str::uuid();
        $data = [
            'permissoesId' => [$permissaoId]
        ];
        // Act
        $response = $this->putJson('/api/permissao/ativar', $data);
        $responseBody = json_decode($response->getContent(), true);
        // Assert
        $response->assertStatus(404);
        $this->assertEquals('Nenhuma permissão encontrada.', $responseBody['message']);
    }
    public function test_ativarPermissao_with_validData_returnsOk(): void
    {
        // Arrange
        User::truncate();
        Perfil::truncate();
        PerfilPermissao::truncate();
        Permissao::truncate();
        $user = User::factory()->createOne();
        $perfil = Perfil::where('id', '=', (string)$user->perfil_id)->first();
        $this->seed(PermissaoSeeder::class);
        $permissaoIds = DB::table('permissao')->pluck('id');
        $perfilUsuarioId = DB::table('perfil')->where('nome', '=', (string)$perfil->nome)->first()->id;
        $perfilPermissoes = $permissaoIds->map(function ($permissaoId) use ($perfilUsuarioId) {
            return [
                'perfil_id' => $perfilUsuarioId,
                'permissao_id' => $permissaoId,
                'criado_por' => 'Admin',
                'criado_em' => now(),
                'atualizado_por' => 'Admin',
                'atualizado_em' => now(),
            ];
        });
        DB::table('perfil_permissao')->insert($perfilPermissoes->toArray());
        $this->actingAs($user, 'jwt');
        $permissaoId = $permissaoIds->random();
        $data = [
            'permissoesId' => [$permissaoId]
        ];
        // Act
        $response = $this->putJson('/api/permissao/ativar', $data);
        $responseBody = json_decode($response->getContent(), true);
        // Assert
        $response->assertStatus(200);
        $this->assertEquals('Permissões Atualizadas com sucesso!', $responseBody['message']);
    }
}
