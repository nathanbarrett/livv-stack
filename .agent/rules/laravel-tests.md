---
trigger: glob
globs: tests/**/*.php
---

## Testing

-   All tests are written as Pest tests
-   endpoint testing goes under the `Feature` folder in `tests`
-   use `describe` to capture a group of tests
-   give good, succint group names as an array on the `describe`. Do not give individual tests group names
-   use `it` function to write tests
-   use Http client to mock responses from any external calls in a test
-   write descriptive test names: `it('should send a request to a third party and recieve a 302 redirect response', function () { ... });`
-   favor assertion syntax `expect($something)->toBe($else)`
-   do not overly chain assertions. you can chain multiple assertions on the same `expect` but don't chain expects
-   always assert valid response codes with `$response->assertOk()` or `$response->assertStatus(Response::HTTP_CREATED)
