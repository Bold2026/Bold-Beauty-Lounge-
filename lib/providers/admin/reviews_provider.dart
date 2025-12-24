import 'package:flutter/foundation.dart';
import '../../models/admin/review_model.dart';
import '../../repositories/admin/reviews_repository.dart';

class ReviewsProvider with ChangeNotifier {
  final ReviewsRepository _repository;

  List<ReviewModel> _reviews = [];
  bool _isLoading = false;
  String? _error;

  ReviewsProvider({ReviewsRepository? repository})
      : _repository = repository ?? ReviewsRepository() {
    loadReviews();
  }

  List<ReviewModel> get reviews => _reviews;
  bool get isLoading => _isLoading;
  String? get error => _error;

  void loadReviews() {
    _isLoading = true;
    _error = null;
    notifyListeners();

    _repository.getAllReviews().listen(
      (reviews) {
        _reviews = reviews;
        _isLoading = false;
        _error = null;
        notifyListeners();
      },
      onError: (error) {
        _error = 'Erreur lors du chargement des avis: $error';
        _isLoading = false;
        notifyListeners();
      },
    );
  }

  Future<bool> addReview(ReviewModel review) async {
    try {
      await _repository.addReview(review);
      return true;
    } catch (e) {
      _error = 'Erreur lors de l\'ajout: $e';
      notifyListeners();
      return false;
    }
  }

  Future<bool> addReply(String reviewId, ReviewReply reply) async {
    try {
      await _repository.addReply(reviewId, reply);
      return true;
    } catch (e) {
      _error = 'Erreur lors de l\'ajout de la réponse: $e';
      notifyListeners();
      return false;
    }
  }

  Future<bool> deleteReview(String reviewId) async {
    try {
      await _repository.deleteReview(reviewId);
      return true;
    } catch (e) {
      _error = 'Erreur lors de la suppression: $e';
      notifyListeners();
      return false;
    }
  }

  Future<bool> deleteReply(String reviewId, String replyId) async {
    try {
      await _repository.deleteReply(reviewId, replyId);
      return true;
    } catch (e) {
      _error = 'Erreur lors de la suppression de la réponse: $e';
      notifyListeners();
      return false;
    }
  }
}




