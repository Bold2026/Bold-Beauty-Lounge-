import 'package:flutter/material.dart';

class HeroAnimations {
  static const Duration defaultDuration = Duration(milliseconds: 300);
  static const Curve defaultCurve = Curves.easeInOut;

  // Hero animation pour les images de services
  static Widget serviceImageHero({
    required String tag,
    required String imagePath,
    required double width,
    required double height,
    BoxFit fit = BoxFit.cover,
  }) {
    return Hero(
      tag: tag,
      child: Container(
        width: width,
        height: height,
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(12),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.2),
              blurRadius: 8,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: ClipRRect(
          borderRadius: BorderRadius.circular(12),
          child: Image.asset(
            imagePath,
            fit: fit,
            errorBuilder: (context, error, stackTrace) {
              return Container(
                color: Colors.grey[300],
                child: const Icon(Icons.spa, color: Colors.grey),
              );
            },
          ),
        ),
      ),
    );
  }

  // Hero animation pour les cartes de spécialistes
  static Widget specialistCardHero({
    required String tag,
    required Widget child,
  }) {
    return Hero(
      tag: tag,
      child: Material(color: Colors.transparent, child: child),
    );
  }

  // Hero animation pour les boutons
  static Widget buttonHero({required String tag, required Widget child}) {
    return Hero(
      tag: tag,
      child: Material(color: Colors.transparent, child: child),
    );
  }

  // Page route avec animation personnalisée
  static PageRouteBuilder<T> createRoute<T extends Object?>(
    Widget page, {
    Duration duration = defaultDuration,
    Curve curve = defaultCurve,
    String? heroTag,
  }) {
    return PageRouteBuilder<T>(
      pageBuilder: (context, animation, secondaryAnimation) => page,
      transitionDuration: duration,
      reverseTransitionDuration: duration,
      transitionsBuilder: (context, animation, secondaryAnimation, child) {
        if (heroTag != null) {
          return Hero(tag: heroTag, child: child);
        }

        const begin = Offset(1.0, 0.0);
        const end = Offset.zero;
        const curve = Curves.easeInOut;

        var tween = Tween(
          begin: begin,
          end: end,
        ).chain(CurveTween(curve: curve));

        return SlideTransition(position: animation.drive(tween), child: child);
      },
    );
  }

  // Animation de fade in
  static Widget fadeIn({
    required Widget child,
    Duration duration = defaultDuration,
    Curve curve = defaultCurve,
  }) {
    return TweenAnimationBuilder<double>(
      duration: duration,
      curve: curve,
      tween: Tween(begin: 0.0, end: 1.0),
      builder: (context, value, child) {
        return Opacity(opacity: value, child: child);
      },
      child: child,
    );
  }

  // Animation de slide in
  static Widget slideIn({
    required Widget child,
    Offset begin = const Offset(0, 1),
    Duration duration = defaultDuration,
    Curve curve = defaultCurve,
  }) {
    return TweenAnimationBuilder<Offset>(
      duration: duration,
      curve: curve,
      tween: Tween(begin: begin, end: Offset.zero),
      builder: (context, value, child) {
        return Transform.translate(offset: value, child: child);
      },
      child: child,
    );
  }

  // Animation de scale in
  static Widget scaleIn({
    required Widget child,
    double begin = 0.0,
    Duration duration = defaultDuration,
    Curve curve = defaultCurve,
  }) {
    return TweenAnimationBuilder<double>(
      duration: duration,
      curve: curve,
      tween: Tween(begin: begin, end: 1.0),
      builder: (context, value, child) {
        return Transform.scale(scale: value, child: child);
      },
      child: child,
    );
  }

  // Animation de rotation
  static Widget rotateIn({
    required Widget child,
    double begin = 0.0,
    Duration duration = defaultDuration,
    Curve curve = defaultCurve,
  }) {
    return TweenAnimationBuilder<double>(
      duration: duration,
      curve: curve,
      tween: Tween(begin: begin, end: 1.0),
      builder: (context, value, child) {
        return Transform.rotate(
          angle: value * 2 * 3.14159, // 2π radians = 360°
          child: child,
        );
      },
      child: child,
    );
  }
}

// Widget pour les animations de liste
class AnimatedListView extends StatelessWidget {
  final List<Widget> children;
  final Duration delay;
  final Duration duration;
  final Curve curve;

  const AnimatedListView({
    super.key,
    required this.children,
    this.delay = const Duration(milliseconds: 100),
    this.duration = const Duration(milliseconds: 300),
    this.curve = Curves.easeInOut,
  });

  @override
  Widget build(BuildContext context) {
    return ListView.builder(
      itemCount: children.length,
      itemBuilder: (context, index) {
        return HeroAnimations.fadeIn(
          duration: Duration(
            milliseconds:
                duration.inMilliseconds + (index * delay.inMilliseconds),
          ),
          curve: curve,
          child: HeroAnimations.slideIn(
            begin: const Offset(0, 0.3),
            duration: Duration(
              milliseconds:
                  duration.inMilliseconds + (index * delay.inMilliseconds),
            ),
            curve: curve,
            child: children[index],
          ),
        );
      },
    );
  }
}

// Widget pour les animations de grille
class AnimatedGridView extends StatelessWidget {
  final List<Widget> children;
  final int crossAxisCount;
  final Duration delay;
  final Duration duration;
  final Curve curve;

  const AnimatedGridView({
    super.key,
    required this.children,
    this.crossAxisCount = 2,
    this.delay = const Duration(milliseconds: 50),
    this.duration = const Duration(milliseconds: 300),
    this.curve = Curves.easeInOut,
  });

  @override
  Widget build(BuildContext context) {
    return GridView.builder(
      gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: crossAxisCount,
        crossAxisSpacing: 8,
        mainAxisSpacing: 8,
      ),
      itemCount: children.length,
      itemBuilder: (context, index) {
        return HeroAnimations.scaleIn(
          begin: 0.0,
          duration: Duration(
            milliseconds:
                duration.inMilliseconds + (index * delay.inMilliseconds),
          ),
          curve: curve,
          child: children[index],
        );
      },
    );
  }
}





