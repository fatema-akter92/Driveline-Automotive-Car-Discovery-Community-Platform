# Driveline UML Class Diagram Checklist (Detailed)

## 1) Scope Confirmed
- Read and traced: `controllers/*.php`, `pages/*.php`, `pages/*.html`, `repositories/*.php`, `services/validation.php`, `docs/*.html`, `README.md`.
- Primary architecture style in codebase: procedural PHP with embedded SQL/procedure calls.
- UML class model in `docs/class-diagram.puml` is the normalized OO design equivalent of implemented behavior.

---

## 2) Class Groups Included

### A. Domain Entities
- `User`
- `AdminUser` (inherits `User`)
- `SessionState`
- `Car`
- `CarImage`
- `CarRatingSummary`
- `SearchCriteria`
- `Review`
- `ReviewLike`
- `Post`
- `PostReaction`
- `Comment`
- `UserType` (enum)
- `ReactionType` (enum)

### B. Controllers
- `AuthController`
- `RegisterController`
- `LogoutController`
- `SessionGuardController`
- `HomeController`
- `SearchController`
- `CarController`
- `CommunityController`
- `ProfileController`
- `AdminCarController`
- `ReviewUpdateController`

### C. Services
- `AuthService`
- `RegistrationService`
- `SessionService`
- `HomeService`
- `SearchService`
- `CarService`
- `ReviewService`
- `CommunityService`
- `ProfileService`
- `ImageStorageService`

### D. Repositories / Infrastructure
- `DatabaseConnection`
- `UserRepository`
- `CarRepository`
- `ReviewRepository`
- `CommunityRepository`
- `SessionRepository`
- `FileStorageGateway`

---

## 3) Cardinality Matrix (Must Draw)

- `User (1) -> (0..*) SessionState`
- `User (1) -> (0..*) Review`
- `Car (1) -> (0..*) Review`
- `Car (1) *-> (1..*) CarImage` (composition)
- `Car (1) -> (0..1) CarRatingSummary`
- `Review (1) -> (0..*) ReviewLike`
- `User (1) -> (0..*) ReviewLike`
- `User (1) -> (0..*) Post`
- `Post (1) -> (0..*) Comment`
- `User (1) -> (0..*) Comment`
- `Post (1) -> (0..*) PostReaction`
- `User (1) -> (0..*) PostReaction`
- `AdminUser --|> User` (inheritance)

---

## 4) File-to-Class Traceability

### Authentication
- `controllers/login.php`
  - `AuthController.login(...)`
  - `AuthService.authenticate(...)`
  - `SessionService.startSession(...)`
  - `UserRepository.findByEmail(...)`
- `controllers/register.php`
  - `RegisterController.register(...)`
  - `RegistrationService.registerUser(...)`
  - `UserRepository.emailExists(...)`, `insertUser(...)`
- `controllers/logout.php`
  - `LogoutController.logout()`
  - `SessionService.destroySession()`
- `controllers/page_controller.php`
  - `SessionGuardController.guard(...)`
  - `SessionService.validateSessionOrFail(...)`, `enforceTimeout(...)`, `refreshSessionActivity(...)`

### Home / Search / Car
- `pages/homepage.php`
  - `HomeController.viewHomepage(...)`
  - `HomeService.getHomepageData(...)`
  - `CarRepository.getThumbnail(...)`, `getFourOtherCars(...)`, `getCarAverageReview(...)`
  - `UserRepository.isUserAdmin(...)`
- `pages/search.php`
  - `SearchController.search(criteria)`
  - `SearchService.searchCars(...)`
  - `CarRepository.searchCars(...)`, `getCarAverageReview(...)`
- `pages/car.php`
  - `CarController.viewCar(...)`
  - `CarService.getCarDetailsWithImages(...)`, `getCarRating(...)`
  - `ReviewService.createReview/editReview/deleteReview/addLike/removeLike/...`
  - `ReviewRepository` methods including duplicate check and like-state queries
- `controllers/update_review.php`
  - `ReviewUpdateController.updateReview(...)`

### Community
- `pages/community.php`
  - `CommunityController.viewCommunity(...)`
  - `CommunityController.handlePostAction(...)`
  - `CommunityService.createPost/editPost/deletePost/togglePostReaction/addComment(...)`
  - `CommunityRepository.getPostsFull(...)`, `getCommentsByPostIds(...)`

### Profile
- `pages/userprofile.php`
  - `ProfileController.viewProfile(...)`
  - `ProfileService.getProfileBySessionUser(...)`
- `pages/edit_profile.php`
  - `ProfileController.updateProfile(...)`
  - `ProfileService.validateDiuEmail(...)`, `updateProfile(...)`
  - `ImageStorageService.saveProfileUpload(...)`

### Admin Car Management
- `pages/push_car.php`
  - `AdminCarController.addCarWithImages(...)`
  - `CarService.addCar(...)`, `addCarImage(...)`
  - `CarRepository.insertCar(...)`, `insertCarImage(...)`
  - Client-side validation occurs directly in page script; no dedicated backend validation class in current implementation

### Infrastructure
- `repositories/db_connect.php`
  - `DatabaseConnection.connect()`

---

## 5) Stored Procedure Mapping (Repository Operations)

### CarRepository
- `Get_Thumbnail`
- `Get_Four_Other_Cars`
- `GetCarDetailsWithImages`
- `search_cars`
- `GetCarAverageReview`
- `InsertCar`
- `InsertCarImage`

### ReviewRepository
- `GiveReview`
- `EditReview`
- `DeleteReview`
- `AddReviewLike`
- `RemoveReviewLike`
- `GetCarReviews`

### CommunityRepository
- `AddPost`
- `TogglePostLike`
- `AddComment`
- `GetPostsFull`

### UserRepository
- `IsUserAdmin`
- `get_user_profile_by_session`
- `update_user_profile`

---

## 6) Packaging & Layer Rules (for diagram quality)
- Controllers depend on Services only.
- Services depend on Repositories and Domain entities.
- Repositories depend on `DatabaseConnection`.
- Upload operations go through `ImageStorageService` -> `FileStorageGateway`.
- Domain entities should not depend on Controller classes.

---

## 7) Final Diagram Files
- Full class diagram source: `docs/class-diagram.puml`
- This checklist: `docs/class-diagram-checklist.md`

Use this checklist as your verification sheet before final submission/export.
