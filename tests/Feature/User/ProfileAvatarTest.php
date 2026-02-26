

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use Laravel\Sanctum\Sanctum;

test('user can upload avatar', function () {
    $this->withoutExceptionHandling();
    Storage::fake('public');
    
    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('avatar.jpg');

    Sanctum::actingAs($user, ['*']);

    $response = $this->postJson('/api/profile/update', [
        'avatar' => $file,
    ]);

    $response->assertStatus(200);

    // Assert the file was stored...
    Storage::disk('public')->assertExists('avatars/' . $file->hashName());
    
    // Assert the file path was saved to the user...
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'avatar_url' => 'avatars/' . $file->hashName(),
    ]);
});

test('avatar is deleted when replacing with a new one', function () {
    Storage::fake('public');
    
    $user = User::factory()->create();
    
    // Upload first avatar
    $file1 = UploadedFile::fake()->image('avatar1.jpg');
    $this->actingAs($user)->postJson('/api/profile/update', [
        'avatar' => $file1,
    ]);
    
    $path1 = 'avatars/' . $file1->hashName();
    Storage::disk('public')->assertExists($path1);

    // Upload second avatar
    $file2 = UploadedFile::fake()->image('avatar2.jpg');
    $this->actingAs($user)->postJson('/api/profile/update', [
        'avatar' => $file2,
    ]);
    
    $path2 = 'avatars/' . $file2->hashName();

    Storage::disk('public')->assertExists($path2);
    Storage::disk('public')->assertMissing($path1);
    
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'avatar_url' => $path2,
    ]);
});
