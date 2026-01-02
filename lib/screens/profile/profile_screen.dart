import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'package:bold_beauty_lounge/provider/user_provider.dart';
import 'package:bold_beauty_lounge/widgets/horizontal_line.dart';

class ProfileScreen extends StatelessWidget {
  // final User? user;
  const ProfileScreen({
    super.key,
  });

  @override
  Widget build(BuildContext context) {
    final user = Provider.of<UserProvider>(context, listen: false).getUser();

    //print(user);
    return Scaffold(
      body: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 40),
        child: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                "Mon profil",
                style: TextStyle(
                    color: Colors.black,
                    fontWeight: FontWeight.bold,
                    fontSize: 32),
              ),
              const SizedBox(
                height: 12.0,
              ),
              const HorizontalLine(),
              const SizedBox(
                height: 20.0,
              ),
              Row(
                // mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  CircleAvatar(
                    radius: 35,
                    foregroundImage: NetworkImage(user!.photoURL.toString()),
                  ),
                  const SizedBox(
                    width: 20,
                  ),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(user.displayName.toString(),
                          style: const TextStyle(
                            fontSize: 24,
                            color: Colors.black,
                            fontWeight: FontWeight.bold,
                          )),
                      Text(user.email.toString(),
                          style: TextStyle(
                            color: Colors.grey.withOpacity(0.8),
                            fontWeight: FontWeight.bold,
                          )),
                    ],
                  )
                ],
              ),
              const SizedBox(
                height: 40,
              ),
              const SectionCard(
                header: "Mes commandes",
                desc: "Vous avez déjà 12 commandes",
              ),
              const SectionCard(
                header: "Adresse de livraison",
                desc:
                    "Robert Robertson, 1234 NW Bobcat Lane, St. Robert, MO 65584-5678.",
              ),
              const SectionCard(
                header: "Méthode de paiement",
                desc: "Visa **34",
              ),
              const SectionCard(
                header: "Codes promo",
                desc: "Vous avez des offres spéciales",
              ),
              const SectionCard(
                header: "Mes avis",
                desc: "Avis pour 4 coiffeurs",
              ),
              const SectionCard(
                header: "Paramètres",
                desc: "Notifications, mot de passe",
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class SectionCard extends StatelessWidget {
  final String header;
  final String desc;
  const SectionCard({
    super.key,
    required this.header,
    required this.desc,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          header,
          style: const TextStyle(
              color: Colors.black, fontWeight: FontWeight.bold, fontSize: 22),
        ),
        const SizedBox(
          height: 4,
        ),
        Text(desc,
            style: TextStyle(
              color: Colors.grey.withOpacity(0.8),
            )),
        const SizedBox(height: 12),
        const HorizontalLine(),
        const SizedBox(height: 12),
      ],
    );
  }
}
